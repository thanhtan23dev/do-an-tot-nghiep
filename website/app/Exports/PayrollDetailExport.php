<?php

namespace App\Exports;

use App\Models\MonthlyPayroll;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollDetailExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $payrollId;

    public function __construct($payrollId)
    {
        $this->payrollId = $payrollId;
    }

    public function view(): View
    {
        // Lấy phiếu lương kèm thông tin nhân viên
        $payroll = MonthlyPayroll::with('worker')->findOrFail($this->payrollId);
        $employee = $payroll->worker;
        
        // Lấy task và group by order y hệt như view web
        $payrollTasks = $payroll->tasks()
            ->with(['order', 'stonePrice'])
            ->get()
            ->groupBy(function ($task) {
                return $task->order ? $task->order->name : 'Công việc lẻ (Không gắn với đơn hàng)';
            });

        return view('exports.payroll_detail', compact('payroll', 'employee', 'payrollTasks'));
    }

    public function styles(Worksheet $sheet)
    {
        // Không cần style phức tạp ở đây vì html inline css đã lo phần border
        return [];
    }
}