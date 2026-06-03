<?php

namespace App\Exports;

use App\Models\NhanVien;
use App\Models\MonthlyPayroll;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class PayrollSummaryExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $nhanVienId;
    protected $months;

    public function __construct($nhanVienId, $months = 6)
    {
        $this->nhanVienId = $nhanVienId;
        $this->months = $months;
    }

    public function view(): View
    {
        $employee = NhanVien::findOrFail($this->nhanVienId);
        
        $startDate = Carbon::now()->startOfMonth()->subMonths($this->months - 1);

        $payrolls = MonthlyPayroll::where('nhan_vien_id', $this->nhanVienId)
            ->whereRaw("STR_TO_DATE(CONCAT(year, '-', month, '-01'), '%Y-%m-%d') >= ?", [$startDate->format('Y-m-01')])
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return view('exports.payroll_summary', [
            'employee' => $employee,
            'payrolls' => $payrolls,
            'months_count' => $this->months
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => 'center']],
            2 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => 'center']],
        ];
    }
}