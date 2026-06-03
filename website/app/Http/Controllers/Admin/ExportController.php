<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\PayrollSummaryExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\NhanVien;
use Illuminate\Support\Str;
use App\Models\MonthlyPayroll;
use App\Exports\PayrollDetailExport;

class ExportController extends Controller
{
    public function exportPayroll(Request $request, $id)
    {
    $employee = NhanVien::findOrFail($id);
    $months = $request->query('months', 6);
    $fileName = 'tong-hop-' . $months . 'thang-' . Str::slug($employee->name) . '.xlsx';
    return Excel::download(new PayrollSummaryExport($id, $months), $fileName);
    }

    public function exportDetailPayroll($id)
    {
        $payroll = MonthlyPayroll::with('worker')->findOrFail($id);
        
        $fileName = 'bien-ban-nghiem-thu-thang-' . str_pad($payroll->month, 2, '0', STR_PAD_LEFT) 
                  . '-' . $payroll->year . '-' . Str::slug($payroll->worker->name) . '.xlsx';
        
        return Excel::download(new PayrollDetailExport($id), $fileName);
    }
}