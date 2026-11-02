<?php

namespace App\Http\Controllers;

use App\Services\Claims\ClaimFormLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ClaimFormLinkController extends Controller
{
    public function send(Request $request, ClaimFormLinkService $service)
    {
        $request->merge(['email' => is_string($request->input('email')) ? mb_strtolower(trim($request->input('email'))) : $request->input('email')]);
        $request->validate(['email' => 'required|string|email:rfc|max:50'], [], ['email' => 'recipient email']);
        try {
            $sent = $service->send(Auth::id(), Session::get('location_id'), $request->input('email'));
            return redirect()->route('reimbursement.index')->with('message', 'Claim form link sent to ' . $sent['email'] . '.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (HttpExceptionInterface $exception) {
            return redirect()->route('reimbursement.index')->withErrors(['claim_link_email' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            // Never log the bearer URL, token, customer form data or SMTP credentials.
            Log::error('Claim invitation could not be sent', ['user_id' => Auth::id(), 'location_id' => Session::get('location_id'), 'exception_type' => get_class($exception)]);
            return redirect()->route('reimbursement.index')->withErrors(['claim_link_email' => 'The claim link could not be sent. Check the claim-link migration, mail configuration and public base URL, then try again.']);
        }
    }

    public function show($token, ClaimFormLinkService $service)
    {
        try {
            $context = $service->active($token);
            $nonce = bin2hex(random_bytes(16));
            return $this->privateResponse(view('reimbursements.claim-public-form', compact('context', 'token', 'nonce')), 200, $nonce);
        } catch (HttpExceptionInterface $exception) {
            return $this->unavailable($exception);
        } catch (\Throwable $exception) {
            Log::error('Public claim form could not be opened', ['exception_type' => get_class($exception)]);
            return $this->privateResponse(view('reimbursements.claim-public-message', ['title' => 'Claim form temporarily unavailable',
                'message' => 'Please try again later or contact the store.']), 503);
        }
    }

    public function submit(Request $request, $token, ClaimFormLinkService $service)
    {
        try {
            $service->submit($token, $request->all());
            // Remove the bearer token from the address bar after submission.
            return redirect()->route('claim-public.complete')->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
        } catch (ValidationException $exception) {
            return redirect()->route('claim-public.show', ['token' => $token])->withErrors($exception->validator)
                ->withInput($this->oldInput($request))
                ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
        } catch (HttpExceptionInterface $exception) {
            return $this->unavailable($exception);
        } catch (\Throwable $exception) {
            Log::error('Public claim submission failed', ['exception_type' => get_class($exception)]);
            return $this->privateResponse(view('reimbursements.claim-public-message', ['title' => 'Your claim was not saved',
                'message' => 'We could not save your claim. Please try the original link again or contact the store.']), 503);
        }
    }

    public function complete()
    {
        return $this->privateResponse(view('reimbursements.claim-public-message', ['title' => 'Your claim has been submitted',
            'message' => 'Thank you. The store has received your claim for review. You may close this page.']));
    }

    private function unavailable(HttpExceptionInterface $exception)
    {
        return $this->privateResponse(view('reimbursements.claim-public-message', ['title' => 'Claim link unavailable',
            'message' => $exception->getStatusCode() === 403 ? 'This claim link is no longer available. Please contact the store.' : $exception->getMessage()]), $exception->getStatusCode());
    }

    private function oldInput(Request $request)
    {
        $input = $request->only(['claim_check', 'claim_check_date', 'special_claim_check', 'original_claim_check', 'claim_detail',
            'compensation', 'item', 'first_name', 'last_name', 'address', 'city', 'state', 'zip_code', 'telephone_number',
            'proof_purchase', 'claim_type', 'damaged_desc', 'receipts_attached', 'acknowledged']);
        foreach ($input as $key => $value) if (!is_scalar($value)) unset($input[$key]);
        if (is_array($request->input('items'))) {
            $input['items'] = [];
            foreach (array_slice(array_values($request->input('items')), 0, (int) config('claim_form_links.max_items', 25)) as $item) {
                $row = [];
                foreach (['item_desc', 'material', 'color', 'date_purchase', 'purchase_location', 'amount_paid'] as $key) {
                    if (is_array($item) && isset($item[$key]) && is_scalar($item[$key])) $row[$key] = $item[$key];
                }
                $input['items'][] = $row;
            }
        }
        return $input;
    }

    private function privateResponse($view, $status = 200, $nonce = null)
    {
        $scriptPolicy = $nonce ? "'nonce-" . $nonce . "'" : "'none'";
        return response($view, $status)->withHeaders([
            'Cache-Control' => 'private, no-store, max-age=0', 'Pragma' => 'no-cache',
            'Referrer-Policy' => 'no-referrer', 'X-Content-Type-Options' => 'nosniff', 'X-Frame-Options' => 'DENY',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            'Content-Security-Policy' => "default-src 'none'; style-src 'self' 'unsafe-inline'; font-src 'self'; img-src 'self' data:; script-src " . $scriptPolicy . "; form-action 'self'; frame-ancestors 'none'; base-uri 'none'",
        ]);
    }
}
