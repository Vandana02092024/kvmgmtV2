<?php

namespace App\Services\Claims;

use App\Mail\ClaimFormInvitation;
use App\Reimbursement;
use App\Reimbursement_describe_item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClaimFormLinkService
{
    public function staffContext($userId, $locationId, $lock = false)
    {
        $users = DB::table('users')->where('id', $userId)->where('status', '1');
        $stores = DB::table('locations')->where('id', $locationId)->where('status', '1');
        $assignments = DB::table('user_locations')->where('user', $userId)->where('location', $locationId);
        if ($lock) { $users->lockForUpdate(); $stores->lockForUpdate(); $assignments->lockForUpdate(); }
        $user = $users->first(); $store = $stores->first();
        abort_unless($user && in_array($user->role, ['Owner', 'Supervisor', 'Employee'], true) && $store && $assignments->first(), 403,
            'Select an active store assigned to your account before sending a claim link.');
        return ['user' => $user, 'store' => $store];
    }

    public function send($userId, $locationId, $email)
    {
        $email = mb_strtolower(trim((string) $email));
        Validator::make(['email' => $email], ['email' => 'required|string|email:rfc|max:50'])->validate();
        $baseUrl = rtrim((string) config('claim_form_links.base_url'), '/');
        $parts = parse_url($baseUrl);
        if (!$parts || !in_array($parts['scheme'] ?? '', ['http', 'https'], true) || empty($parts['host']) ||
            isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new \RuntimeException('Configure a valid CLAIM_FORM_BASE_URL before sending claim links.');
        }
        $token = bin2hex(random_bytes(32));
        $expires = now()->addDays(max(1, min(30, (int) config('claim_form_links.expires_days', 7))));
        $invitation = DB::transaction(function () use ($userId, $locationId, $email, $token, $expires) {
            $context = $this->staffContext($userId, $locationId, true);
            $id = DB::table('claim_form_links')->insertGetId([
                'location_id' => $context['store']->id, 'invited_by' => $context['user']->id, 'email' => $email,
                'token_hash' => hash('sha256', $token), 'expires_at' => $expires->format('Y-m-d H:i:s'),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            return ['id' => $id, 'store' => $context['store']];
        });
        try {
            // Mail is sent synchronously, outside the database lock. Only a
            // successfully sent invitation is enabled for public submissions.
            Mail::to($email)->send(new ClaimFormInvitation($baseUrl . '/claim-form/' . $token,
                $invitation['store']->location, $expires->format('M j, Y g:i A T')));
            DB::table('claim_form_links')->where('id', $invitation['id'])->update(['sent_at' => now(), 'updated_at' => now()]);
        } catch (\Throwable $exception) {
            DB::table('claim_form_links')->where('id', $invitation['id'])->update(['revoked_at' => now(), 'updated_at' => now()]);
            throw $exception;
        }
        return ['id' => $invitation['id'], 'email' => $email];
    }

    public function active($token, $lock = false)
    {
        abort_unless(is_string($token) && preg_match('/\A[a-f0-9]{64}\z/D', $token), 404, 'This claim link is invalid.');
        $query = DB::table('claim_form_links')->where('token_hash', hash('sha256', $token));
        if ($lock) $query->lockForUpdate();
        $link = $query->first();
        abort_unless($link, 404, 'This claim link is invalid.');
        abort_if(!$link->sent_at || $link->revoked_at, 410, 'This claim link is unavailable. Please ask the store for a new link.');
        abort_if($link->submitted_at || $link->reimbursement_id, 410, 'This claim link has already been submitted. Please contact the store for any changes.');
        abort_if(now()->greaterThanOrEqualTo(\Illuminate\Support\Carbon::parse($link->expires_at)), 410,
            'This claim link has expired. Please ask the store for a new link.');
        $context = $this->staffContext($link->invited_by, $link->location_id, $lock);
        return ['link' => $link, 'store' => $context['store'], 'sender' => $context['user']];
    }

    public function validate(array $input, $email)
    {
        return Validator::make($input, [
            'email_address' => ['required', 'string', Rule::in([$email])],
            'claim_check' => 'nullable|string|max:50',
            'claim_check_date' => 'nullable|date_format:Y-m-d|before_or_equal:today',
            'special_claim_check' => 'nullable|string|max:50',
            'original_claim_check' => 'required|in:0,1',
            'claim_detail' => 'nullable|required_if:original_claim_check,1|string|max:255',
            'compensation' => 'required|in:0,1,2,3',
            'item' => 'required|in:0,1,2,3',
            'first_name' => 'required|string|max:50', 'last_name' => 'required|string|max:50',
            'address' => 'required|string|max:2000', 'city' => 'required|string|max:50',
            'state' => 'required|string|max:50', 'zip_code' => 'required|string|max:50',
            'telephone_number' => 'required|string|max:15',
            'proof_purchase' => ['required', 'numeric', 'min:0.01', 'max:9999999.99', 'regex:/\A\d+(?:\.\d{1,2})?\z/D'],
            'claim_type' => 'required|in:1,2,3', 'damaged_desc' => 'nullable|string|max:255',
            'receipts_attached' => 'required|in:0,1',
            'items' => 'required|array|min:1|max:' . (int) config('claim_form_links.max_items', 25),
            'items.*' => 'required|array:item_desc,material,color,date_purchase,purchase_location,amount_paid',
            'items.*.item_desc' => 'required|string|max:50',
            'items.*.material' => 'nullable|string|max:50', 'items.*.color' => 'nullable|string|max:50',
            'items.*.date_purchase' => 'nullable|date_format:Y-m-d|before_or_equal:today',
            'items.*.purchase_location' => 'nullable|string|max:50',
            'items.*.amount_paid' => ['required', 'numeric', 'min:0', 'max:9999999.99', 'regex:/\A\d+(?:\.\d{1,2})?\z/D'],
            'acknowledged' => 'accepted',
        ], ['email_address.in' => 'The email address is fixed to the recipient of this claim link.'])->validate();
    }

    public function submit($token, array $input)
    {
        return DB::transaction(function () use ($token, $input) {
            $context = $this->active($token, true);
            $data = $this->validate($input, $context['link']->email);
            $totalCents = 0;
            foreach ($data['items'] as $item) $totalCents += $this->cents($item['amount_paid']);
            // Every legacy non-null field gets a deliberate default. Public
            // input never supplies store, staff, IDs, status or payment fields.
            $record = array_fill_keys((new Reimbursement)->getFillable(), '');
            foreach (['claim_check', 'special_claim_check', 'first_name', 'last_name', 'address', 'city', 'state',
                'zip_code', 'telephone_number', 'original_claim_check', 'claim_detail', 'compensation', 'item',
                'claim_type', 'damaged_desc', 'receipts_attached'] as $field) $record[$field] = $data[$field] ?? '';
            $record['claim_check_date'] = $this->legacyDate($data['claim_check_date'] ?? null);
            $record['email_address'] = $context['link']->email;
            $record['today_date'] = now()->format('m/d/Y');
            $record['location_id'] = $context['store']->id;
            $record['store'] = $context['store']->location;
            $record['user_id'] = $context['sender']->id;
            $record['zip_type'] = 0; $record['report_id'] = 0;
            $record['total_desc'] = $this->decimal($totalCents);
            $record['proof_purchase'] = $this->decimal($this->cents($data['proof_purchase']));
            $record['status'] = $context['sender']->role !== 'Owner' &&
                $this->cents($data['proof_purchase']) > (int) round((float) $context['sender']->claim * 100)
                ? 'Approved Pending' : 'Pending';
            $claim = Reimbursement::create($record);
            foreach ($data['items'] as $item) {
                Reimbursement_describe_item::create([
                    'reimburse_id' => $claim->id, 'item_desc' => $item['item_desc'], 'material' => $item['material'] ?? '',
                    'color' => $item['color'] ?? '', 'date_purchase' => $this->legacyDate($item['date_purchase'] ?? null),
                    'purchase_location' => $item['purchase_location'] ?? '', 'amount_paid' => $this->decimal($this->cents($item['amount_paid'])),
                ]);
            }
            DB::table('claim_form_links')->where('id', $context['link']->id)->update([
                'submitted_at' => now(), 'reimbursement_id' => $claim->id, 'updated_at' => now(),
            ]);
            return $claim;
        });
    }

    private function cents($value)
    {
        $parts = explode('.', (string) $value, 2);
        return (int) $parts[0] * 100 + (int) str_pad($parts[1] ?? '', 2, '0');
    }

    private function decimal($cents)
    {
        return intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    private function legacyDate($date)
    {
        return $date ? \DateTimeImmutable::createFromFormat('!Y-m-d', $date)->format('m/d/Y') : '';
    }
}
