<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MonthlyPayroll;
use Illuminate\Support\Facades\Validator;

class SalaryAdvanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $payrolls = MonthlyPayroll::where('nhan_vien_id', $user->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $payrolls
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $payroll = MonthlyPayroll::with('tasks')
            ->where('nhan_vien_id', $user->id)
            ->find($id);

        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiếu lương này hoặc bạn không có quyền xem.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $payroll
        ], 200);
    }

    public function create(Request $request)
    {
        $user = $request->user();

        $pendingAmount = MonthlyPayroll::where('nhan_vien_id', $user->id)
            ->where('status', 'pending')
            ->sum('withdrawn_amount');

        $availableBalance = $user->total_balance - $pendingAmount;

        return response()->json([
            'success' => true,
            'data' => [
                'total_balance' => $user->total_balance,
                'pending_amount' => $pendingAmount,
                'available_balance' => $availableBalance
            ]
        ], 200);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $pendingAmount = MonthlyPayroll::where('nhan_vien_id', $user->id)
            ->where('status', 'pending')
            ->sum('withdrawn_amount');

        $availableBalance = $user->total_balance - $pendingAmount;
        $validator = Validator::make($request->all(), [
            'withdrawn_amount' => [
                'required',
                'numeric',
                'min:10000',
                'max:' . $availableBalance
            ]
        ], [
            'withdrawn_amount.required' => 'Vui lòng nhập số tiền muốn ứng.',
            'withdrawn_amount.numeric'  => 'Số tiền nhập vào không hợp lệ.',
            'withdrawn_amount.min'      => 'Số tiền muốn ứng tối thiểu là 10,000 VNĐ.',
            'withdrawn_amount.max'      => 'Số tiền ứng vượt quá số dư khả dụng (' . number_format($availableBalance) . ' VNĐ).'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $payroll = MonthlyPayroll::create([
                'nhan_vien_id'     => $user->id,
                'month'            => now()->month,
                'year'             => now()->year,
                'withdrawn_amount' => $request->withdrawn_amount,
                'status'           => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã gửi yêu cầu ứng lương thành công. Vui lòng chờ quản lý duyệt.',
                'data'    => $payroll
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
