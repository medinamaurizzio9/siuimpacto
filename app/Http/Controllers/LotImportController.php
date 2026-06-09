<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use App\Services\LotCsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LotImportController extends Controller
{
    public function create(): View
    {
        return view('lotes.import.form');
    }

    public function preview(Request $request, LotCsvImportService $importer): View
    {
        $request->validate(['csv' => ['required', 'file', 'mimes:csv,txt']]);
        $result = $importer->parse($request->file('csv'));

        if (empty($result['errors'])) {
            session(['lotes_import_rows' => $result['rows']]);
        } else {
            session()->forget('lotes_import_rows');
        }

        return view('lotes.import.preview', $result);
    }

    public function store(Request $request, LotCsvImportService $importer, AuditService $auditService): RedirectResponse
    {
        $rows = session('lotes_import_rows', []);

        if (empty($rows) || ! is_array($rows)) {
            return redirect()
                ->route('lotes.import.create')
                ->withErrors('No existen datos validos para importar.');
        }

        $count = $importer->import($rows);
        session()->forget('lotes_import_rows');

        $auditService->log(null, 'importar_lotes_csv', "Se importaron {$count} lotes.", null, ['total' => $count], $request);

        return redirect()->route('lotes.index')->with('status', "Se importaron {$count} lotes correctamente.");
    }
}
