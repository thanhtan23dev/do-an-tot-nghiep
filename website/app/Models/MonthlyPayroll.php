<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyPayroll extends Model
{
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        // 1. TRƯỚC KHI TẠO PHIẾU: Tự động gom "Giá trị nghiệm thu" từ các Task CHƯA ĐÓNG DẤU
        static::creating(function ($payroll) {
            $earned = Task::where('worker_id', $payroll->nhan_vien_id)
                ->where('status', 'completed')
                ->whereNull('monthly_payroll_id')
                ->sum('reward');

            $payroll->earned_amount = $earned;

            $payroll->deduction_note = $payroll->deduction_note ?? '';

            $worker = $payroll->worker ?? NhanVien::find($payroll->nhan_vien_id);
            if ($worker) {
                $pendingAmount = self::where('nhan_vien_id', $worker->id)
                    ->where('status', 'pending')
                    ->sum('withdrawn_amount');

                // Số tiền thực tế còn có thể ứng
                $availableBalance = $worker->total_balance - $pendingAmount;

                if ($payroll->withdrawn_amount > $availableBalance) {
                    throw new \Exception("Lỗi: Số dư khả dụng để ứng chỉ còn " . number_format($availableBalance) . " VNĐ (Số dư : " . number_format($worker->total_balance) . " VNĐ, Đang chờ duyệt: " . number_format($pendingAmount) . " VNĐ).");
                }
            }
        });

        // 2. VỪA TẠO PHIẾU XONG: CHỈ đóng dấu Task, CHƯA trừ tiền Két Sắt
        static::created(function ($payroll) {
            // Đóng dấu các Task đã nghiệm thu vào phiếu này
            Task::where('worker_id', $payroll->nhan_vien_id)
                ->where('status', 'completed')
                ->whereNull('monthly_payroll_id')
                ->update(['monthly_payroll_id' => $payroll->id]);

            // Nếu phiếu được tạo thẳng với trạng thái 'paid' thì trừ luôn
            if ($payroll->status === 'paid' && $payroll->worker) {
                $totalDeduction = $payroll->withdrawn_amount + $payroll->deduction_amount;
                $payroll->worker->total_balance -= $totalDeduction;
                $payroll->worker->save();
            }
        });

        // 3. KHI SẾP CHỈNH SỬA PHIẾU LƯƠNG
        static::updating(function ($payroll) {
            $originalStatus = $payroll->getOriginal('status');

            // [KHIÊN BẢO VỆ 1]: Nếu phiếu ĐÃ THANH TOÁN (paid), cấm sửa các thông tin khác, CHỈ được phép đổi status về pending
            if ($originalStatus === 'paid') {
                $dirty = $payroll->getDirty();
                $realChanges = [];

                foreach ($dirty as $field => $newValue) {
                    
                    if (in_array($field, ['status', 'updated_at'])) {
                        continue;
                    }
                    $oldValue = $payroll->getOriginal($field);
                    if (empty($oldValue) && empty($newValue)) {
                        continue;
                    }

                    if (is_numeric($oldValue) && is_numeric($newValue)) {
                        if ((float)$oldValue === (float)$newValue) {
                            continue;
                        }
                    }
                    if ((string)$oldValue === (string)$newValue) {
                        continue;
                    }
                    $realChanges[] = $field;
                }
                if (count($realChanges) > 0) {
                    $errorMsg = "Phiếu đã thanh toán! KHÔNG được sửa dữ liệu (Thay đổi trái phép ở trường: " . implode(', ', $realChanges) . "). Nếu cần sửa vui lòng đổi trạng thái về chưa thanh toán trước.";

                    if (request()->expectsJson() || request()->is('api/*')) {
                        throw new \Exception($errorMsg, 400);
                    }

                    throw new \Illuminate\Http\Exceptions\HttpResponseException(
                        redirect()->back()->with([
                            'message'    => $errorMsg,
                            'alert-type' => 'error',
                        ])
                    );
                }
            }

            // CHUYỂN TỪ PENDING -> PAID (Sếp duyệt thanh toán): Trừ tiền Két Sắt
            if ($payroll->isDirty('status') && $originalStatus === 'pending' && $payroll->status === 'paid') {
                if ($payroll->worker) {
                    $totalDeduction = $payroll->withdrawn_amount + $payroll->deduction_amount;
                    $payroll->worker->total_balance -= $totalDeduction;
                    $payroll->worker->save();
                }
            }

            // CHUYỂN TỪ PAID -> PENDING (Sếp thu hồi duyệt): Hoàn lại tiền vào Két Sắt
            if ($payroll->isDirty('status') && $originalStatus === 'paid' && $payroll->status === 'pending') {
                if ($payroll->worker) {
                    $totalRefund = $payroll->getOriginal('withdrawn_amount') + $payroll->getOriginal('deduction_amount');
                    $payroll->worker->total_balance += $totalRefund;
                    $payroll->worker->save();
                }
            }
        });

        // 4. [KHIÊN BẢO VỆ 2]: NGĂN CHẶN XÓA PHIẾU ĐANG Ở TRẠNG THÁI PAID
        static::deleting(function ($payroll) {
            if ($payroll->status === 'paid') {
                $errorMsg = "Không thể xóa phiếu 'Đã thanh toán'.";

                if (request()->expectsJson() || request()->is('api/*')) {
                    throw new \Exception($errorMsg, 400);
                }

                throw new \Illuminate\Http\Exceptions\HttpResponseException(
                    redirect()->back()->with([
                        'message'    => $errorMsg,
                        'alert-type' => 'error',
                    ])
                );
            }
        });

        // 5. KHI XÓA PHIẾU: Gỡ dấu mộc cho Task
        static::deleted(function ($payroll) {
            Task::where('monthly_payroll_id', $payroll->id)
                ->update(['monthly_payroll_id' => null]);
        });
    }

    public function worker()
    {
        return $this->belongsTo(NhanVien::class, 'nhan_vien_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'monthly_payroll_id');
    }
}
