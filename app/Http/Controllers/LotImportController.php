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

        return view('lotes.import.preview', $result);
    }

    public function store(Request $request, LotCsvImportService $importer, AuditService $auditService): RedirectResponse
    {
        $rows = json_decode($request->input('rows', '[]'), true);
        abort_if(! is_array($rows), 422, 'Datos de importacion invalidos.');

        $count = $importer->import($rows);
        $auditService->log(null, 'importar_lotes_csv', "Se importaron {$count} lotes.", null, ['total' => $count], $request);

        return redirect()->route('lotes.index')->with('status', "Se importaron {$count} lotes correctamente.");
    }
}
