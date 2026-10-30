<?php

namespace App\Http\Controllers;

use App\Services\LaborExcel\LaborExcelImporter;
use App\Services\LaborExcel\LaborWorkbookReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class LaborReportExcelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function upload(Request $request, LaborWorkbookReader $reader, LaborExcelImporter $importer)
    {
        $request->validate(['file' => 'required|file|mimes:xls,xlsx|max:' . (int) config('labor_excel.max_file_size_kb', 20480),
            'start_week' => 'required|integer|min:1']);
        try {
            $locationId = Session::get('location_id');
            $importer->authorize($locationId, Auth::id());
            $context = $importer->context($locationId, $request->input('start_week'));
            @set_time_limit(120);
            // Read PHP's private upload temporary file. No publicly accessible copy is retained.
            $report = $reader->read($request->file('file')->getRealPath());
            $catalog = $importer->catalog($context);
            Session::put('labor_excel_preview', ['token' => Str::random(40), 'user_id' => (int) Auth::id(),
                'expires' => time() + 1800, 'context' => $context, 'report' => $report,
                'filename' => $request->file('file')->getClientOriginalName(),
                'mapping' => $importer->suggestions($report, $catalog), 'snapshot' => $importer->snapshot($context)]);
            return redirect()->route('labor-excel.preview');
        } catch (\Throwable $exception) {
            return redirect()->back()->withErrors(['file' => $this->error($exception)]);
        }
    }

    public function preview(LaborExcelImporter $importer)
    {
        try {
            $pending = $this->pending($importer);
            $catalog = $importer->catalog($pending['context']);
            $sources = $importer->sources($pending['report']);
            $employeeOptions = [];
            foreach ($sources as $index => $source) $employeeOptions[$index] = $importer->employeeOptions($source, $index, $catalog);
            $previous = old('token') === $pending['token'] && is_array(old('mapping')) ? old('mapping') : $pending['mapping'];
            $mapping = $importer->previewMapping($pending['report'], $catalog, is_array($previous) ? $previous : []);
            $pending['mapping'] = $mapping;
            Session::put('labor_excel_preview', $pending);
            $planError = null;
            try { $importer->plan($pending['report'], is_array($mapping) ? $mapping : [], $catalog, $pending['context']); }
            catch (\RuntimeException $exception) { $planError = $exception->getMessage(); }
            $headers = $importer->headers($pending['context']);
            $existing = DB::table('laboursections as l')->leftJoin('payroll_employees as e', 'e.id', '=', 'l.empId')
                ->leftJoin('payroll_departments as d', 'd.id', '=', 'l.depId')->leftJoin('payroll_categories as c', 'c.id', '=', 'l.catId')
                ->whereIn('l.header_id', $headers->pluck('id'))->orderBy('l.depId')->orderBy('l.empId')->orderBy('l.weekNo')->orderBy('l.catId')
                ->select('l.*', 'e.name as employee_name', 'd.name as department_name', 'c.name as category_name')->get();
            return response()->view('laboursections.labor-excel-preview', compact('pending', 'catalog', 'sources', 'employeeOptions', 'mapping', 'planError', 'headers', 'existing'))
                ->header('Cache-Control', 'private, no-store');
        } catch (\Throwable $exception) {
            return redirect()->route('laboursection.create')->withErrors(['file' => $this->error($exception)]);
        }
    }

    public function import(Request $request, LaborExcelImporter $importer)
    {
        $request->validate(['token' => 'required|string', 'mapping' => 'required|array', 'reviewed' => 'accepted',
            'replace_existing' => 'sometimes|accepted', 'create_employees' => 'sometimes|accepted']);
        try {
            $pending = $this->pending($importer);
            if (!hash_equals($pending['token'], $request->input('token'))) throw new \RuntimeException('Excel preview expired. Upload again.');
            $result = $importer->save($pending, $request->input('mapping'), Auth::id(), $request->boolean('replace_existing'), $request->boolean('create_employees'));
            Session::forget('labor_excel_preview');
            return redirect()->route('laboursection.edit', $result['header_id'])->with('message',
                'Labor Excel imported: ' . $result['employees_created'] . ' employees created, ' . $result['inserted'] . ' fields added, ' . $result['updated'] . ' updated, ' . $result['unchanged'] . ' unchanged. Period total: ' . number_format($result['total'], 2) . '.');
        } catch (\Throwable $exception) {
            return redirect()->route('labor-excel.preview')->withErrors(['file' => $this->error($exception)])->withInput($request->except('_token'));
        }
    }

    public function cancel(Request $request)
    {
        $pending = Session::get('labor_excel_preview');
        if ($pending && (int) $pending['user_id'] === (int) Auth::id() && is_string($request->input('token')) && hash_equals($pending['token'], $request->input('token'))) {
            Session::forget('labor_excel_preview');
        }
        return redirect()->route('laboursection.create');
    }

    private function pending(LaborExcelImporter $importer)
    {
        $pending = Session::get('labor_excel_preview');
        if (!$pending || $pending['expires'] < time() || (int) $pending['user_id'] !== (int) Auth::id() ||
            (int) $pending['context']['location_id'] !== (int) Session::get('location_id')) throw new \RuntimeException('Excel preview expired or the active store changed. Upload again.');
        $importer->authorize(Session::get('location_id'), Auth::id());
        return $pending;
    }

    private function error(\Throwable $exception)
    {
        \Log::warning('Labor Excel import failed', ['user_id' => Auth::id(), 'exception' => $exception]);
        if ($exception instanceof \RuntimeException && !($exception instanceof \Illuminate\Database\QueryException)) return $exception->getMessage();
        return 'The labor workbook could not be processed. No labor data was saved. Check the server log for details.';
    }
}
