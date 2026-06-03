<?php

namespace App\Http\Controllers\NhanVien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MonthlyPayroll;

class SalaryAdvanceController extends Controller
{
    public function index()
    {
        $user = auth('nhan_vien')->user();

        $payrolls = MonthlyPayroll::where('nhan_vien_id', $user->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('nhanvien.salary_advance.index', compact('payrolls'));
    }

    public function show($id)
    {
        $user = auth('nhan_vien')->user();
        $payroll = MonthlyPayroll::with('tasks')
            ->where('nhan_vien_id', $user->id)
            ->findOrFail($id);

        return view('nhanvien.salary_advance.show', compact('payroll'));
    }

    public function create()
    {
        $user = auth('nhan_vien')->user();
        $pendingAmount = MonthlyPayroll::where('nhan_vien_id', $user->id)
            ->where('status', 'pending')
            ->sum('withdrawn_amount');

        $availableBalance = $user->total_balance - $pendingAmount;

        return view('nhanvien.salary_advance.create', compact('user', 'pendingAmount', 'availableBalance'));
    }

    public function store(Request $request)
    {
        $user = auth('nhan_vien')->user();

        $pendingAmount = MonthlyPayroll::where('nhan_vien_id', $user->id)
            ->where('status', 'pending')
            ->sum('withdrawn_amount');
        $availableBalance = $user->total_balance - $pendingAmount;

        $request->validate([
            'withdrawn_amount' => [
                'required',
                'numeric',
                'min:10000',
                'max:' . $availableBalance
            ]
        ], [
            'withdrawn_amount.required' => 'Vui lòng nhập số tiền muốn ứng.',
            'withdrawn_amount.numeric' => 'Số tiền nhập vào không hợp lệ.',
            'withdrawn_amount.min' => 'Số tiền muốn ứng tối thiểu là 10,000.',
            'withdrawn_amount.max' => 'Số tiền ứng vượt quá số dư khả dụng (' . number_format($availableBalance) . ').'
        ]);

        try {
            MonthlyPayroll::create([
                'nhan_vien_id'     => $user->id,
                'month'            => now()->month,
                'year'             => now()->year,
                'withdrawn_amount' => $request->withdrawn_amount,
                'status'           => 'pending',
            ]);

            return redirect()->route('nhanvien.salary_advance.index')->with('success', 'Đã gửi yêu cầu ứng lương thành công. Vui lòng chờ quản lý duyệt.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }
}
