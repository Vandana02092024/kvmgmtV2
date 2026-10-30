<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Session;
use App\Services\SalesReport\SalesReportPdfReader;
use App\Services\SalesReport\SalesReportPdfImporter;

class SalesReportPdfController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dailyEntryPdf(Request $request, SalesReportPdfReader $reader, SalesReportPdfImporter $importer)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:pdf|max:' . (int) config('sales_report_pdf.max_file_size_kb', 20480),
            'entry_date' => 'required|date_format:m-d-Y',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $locationId = $this->authorizedLocationId();
        if (!$locationId) {
            return redirect()->back()->withErrors(['file' => 'Select a store assigned to your account before uploading.']);
        }

        $path = null;
        try {
            $entryDate = \DateTime::createFromFormat('!m-d-Y', $request->input('entry_date'))->format('Y-m-d');
            $context = $importer->context($locationId, $entryDate);
            $directory = storage_path('app/uploads/sales-report/' . $locationId . '/' . $entryDate);
            $this->ensureDirectory($directory);
            // This legacy deployment also serves the repository root. Deny
            // direct web access; the authenticated preview action serves PDFs.
            File::put($directory . '/.htaccess', "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
            File::put($directory . '/web.config', '<?xml version="1.0"?><configuration><system.webServer><security><authorization><remove users="*" roles="" verbs=""/><add accessType="Deny" users="*"/></authorization></security></system.webServer></configuration>');
            $fileName = 'sales-report-' . \Illuminate\Support\Str::random(40) . '.pdf';
            $request->file('file')->move($directory, $fileName);
            $path = $directory . DIRECTORY_SEPARATOR . $fileName;
            // OCR can span several scanned pages. Individual commands also have
            // bounded timeouts and page/file limits in the sales report PDF tools.
            @set_time_limit(600);
            $report = $reader->read($path);
            if ($report['report_date'] !== $entryDate) {
                throw new \RuntimeException('PDF date is ' . $report['report_date'] . ', but the selected day is ' . $entryDate . '. Select the matching day and upload again.');
            }
            $catalog = $importer->catalog($context);
            $sources = $importer->sources($report);
            $mapping = array();
            foreach ($sources as $index => $source) {
                $target = $importer->suggestedTarget($source, $catalog);
                $mapping[$index] = $target ?: ((float) $source['amount'] == 0 && ($source['unit'] === '' || (float) $source['unit'] == 0) ? 'skip' : '');
            }
            // Keep amounts on the server. The browser submits mapping choices
            // and a one-time token, never trusted monetary values or table names.
            unset($report['raw_text']);
            $pending = array('token' => \Illuminate\Support\Str::random(40), 'user_id' => Auth::id(), 'expires' => time() + 1800,
                'path' => $path, 'filename' => $request->file('file')->getClientOriginalName(),
                'context' => $context, 'report' => $report, 'sources' => $sources, 'mapping' => $mapping,
                'daily_metrics' => isset($report['daily_metrics']) ? $report['daily_metrics'] : array(),
                'snapshot' => $importer->snapshot($context));
            $this->discardPendingPdf();
            Session::put('daily_sales_pdf', $pending);
            return redirect()->route('sales-pdf.preview');
        } catch (\Throwable $exception) {
            if ($path && is_file($path)) {
                @unlink($path);
            }
            \Log::warning('Daily sales PDF preview failed', array('user_id' => Auth::id(), 'reason' => $exception->getMessage(), 'exception' => $exception));
            return redirect()->back()->withErrors(array('file' => $this->pdfError($exception)));
        }
    }

    public function dailyEntryPdfPreview(SalesReportPdfImporter $importer)
    {
        $pending = $this->pendingPdf();
        if (!$pending) {
            return redirect()->route('salesection.create')->withErrors(array('file' => 'PDF preview expired or the active store changed. Upload again.'));
        }
        $catalog = $importer->catalog($pending['context']);
        $existing = DB::table('salesections')->where('location_id', $pending['context']['location_id'])
            ->where('dataDate', $pending['context']['dataDate'])->get();
        $existingByField = array();
        foreach ($existing as $row) {
            $key = $row->nameDep . '_' . $row->nameId;
            $existingByField[$key][] = array('unit' => $row->unit, 'amount' => $row->amount,
                'ez_drop_signups' => $row->ez_drop_signups, 'new_customers' => $row->new_customers);
        }
        return view('salesections.sales-report-pdf-preview', compact('pending', 'catalog', 'existingByField'));
    }

    public function dailyEntryPdfFile($token)
    {
        $pending = $this->pendingPdf();
        if (!$pending || !hash_equals($pending['token'], $token) || !is_file($pending['path'])) {
            abort(404);
        }
        return response(file_get_contents($pending['path']), 200, array('Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="daily-sales-report.pdf"', 'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff'));
    }

    public function dailyEntryPdfImport(Request $request, SalesReportPdfImporter $importer)
    {
        $pending = $this->pendingPdf();
        if (!$pending || !is_string($request->input('token')) || !hash_equals($pending['token'], $request->input('token'))) {
            return redirect()->route('salesection.create')->withErrors(array('file' => 'PDF preview expired or the active store changed. Upload again.'));
        }
        $validator = Validator::make($request->all(), array('mapping' => 'required|array', 'reviewed' => 'accepted', 'replace_existing' => 'sometimes|accepted'));
        if ($validator->fails()) {
            return redirect()->route('sales-pdf.preview')->withErrors($validator)->withInput();
        }
        try {
            $result = $importer->save($pending, $request->input('mapping'), Auth::id(), $request->has('replace_existing'));
        } catch (\Throwable $exception) {
            \Log::warning('Daily sales PDF import failed', array('user_id' => Auth::id(), 'reason' => $exception->getMessage(), 'exception' => $exception));
            return redirect()->route('sales-pdf.preview')->withErrors(array('file' => $this->pdfError($exception)))->withInput();
        }
        $this->discardPendingPdf();
        $record = DB::table('salesections')->where('location_id', $pending['context']['location_id'])->where('dataDate', $pending['context']['dataDate'])->first();
        $message = 'PDF imported for ' . $pending['context']['dataDate'] . ': ' . $result['inserted'] . ' added, ' . $result['updated'] . ' updated, ' . $result['unchanged'] . ' unchanged.';
        return redirect()->route('salesection.edit', 'record-' . $record->id)->with('message', $message)
            ->with('sales_pdf_warnings', $pending['report']['warnings']);
    }

    public function dailyEntryPdfCancel(Request $request)
    {
        $pending = $this->pendingPdf();
        if ($pending && is_string($request->input('token')) && hash_equals($pending['token'], $request->input('token'))) {
            $this->discardPendingPdf();
        }
        return redirect()->route('salesection.create');
    }

    private function pendingPdf()
    {
        $pending = Session::get('daily_sales_pdf');
        $location = $this->authorizedLocationId();
        if (!$pending || $pending['expires'] < time() || $pending['user_id'] != Auth::id() ||
            !$location || $pending['context']['location_id'] != $location) {
            return null;
        }
        return $pending;
    }

    private function discardPendingPdf($deleteFile = true)
    {
        $pending = Session::get('daily_sales_pdf');
        if ($deleteFile && $pending && is_file($pending['path'])) {
            @unlink($pending['path']);
        }
        Session::forget('daily_sales_pdf');
    }

    private function pdfError(\Throwable $exception)
    {
        if (strpos($exception->getMessage(), 'PHP GD extension is disabled') !== false) {
            return 'Enable PHP GD (extension=gd) in the server php.ini and restart Apache before uploading sales PDFs. No sales data was saved.';
        }
        // Database/OCR process errors may contain server paths or SQL. Keep
        // detailed diagnostics in the log and show actionable input errors only.
        if ($exception instanceof \Illuminate\Database\QueryException || !($exception instanceof \RuntimeException) ||
            preg_match('/failed with exit code|toolchain is not ready/i', $exception->getMessage())) {
            return 'The PDF could not be processed. No sales data was saved. Check the PDF reader configuration and server log.';
        }
        return $exception->getMessage();
    }

    private function authorizedLocationId()
    {
        $locationId = Session::get('location_id');
        if (!$locationId || !Auth::user() || !Auth::user()->status) {
            return null;
        }

        $assigned = DB::table('user_locations')
            ->join('locations', 'locations.id', '=', 'user_locations.location')
            ->where('locations.status', '1')
            ->where('user_locations.user', Auth::id())
            ->where('user_locations.location', $locationId)
            ->exists();

        return $assigned ? $locationId : null;
    }

    private function ensureDirectory($directory)
    {
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }
}
