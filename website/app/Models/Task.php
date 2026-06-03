<?php

namespace App\Models;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Task extends Model
{
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        // [KHIÊN BẢO VỆ 1]: NGĂN CHẶN XÓA CÔNG VIỆC ĐÃ HOÀN THÀNH
        static::deleting(function ($task) {
            if ($task->status === 'completed') {
                $errorMsg = "Không thể xóa công việc đã nghiệm thu vì tiền thù lao đã được cộng vào số dư của thợ!";

                if (request()->expectsJson() || request()->is('api/*')) {
                    throw new \Exception($errorMsg, 400);
                }

                // session()->flash('message', $errorMsg);
                // session()->flash('alert-type', 'error');
                // return false;
                throw new HttpResponseException(
                    redirect()->back()->with([
                        'message'    => $errorMsg,
                        'alert-type' => 'error',
                    ])
                );
            }
        });

        // KHI XÓA CÔNG VIỆC (Hợp lệ)
        static::deleted(function ($task) {
            if ($task->order) {
                $task->order->updateStats();
            }
        });

        static::saving(function ($task) {
            if ($task->isDirty(['length', 'width', 'height', 'item_count', 'formula_id', 'stone_price_id'])) {
                // 1. TÍNH KHỐI LƯỢNG (DỰA VÀO CÔNG THỨC D, R, C)
                if (!empty($task->formula_id)) {
                    $formula = Formula::find($task->formula_id);

                    if ($formula) {
                        $mathString = $formula->expression;
                        $mathString = str_ireplace(
                            ['D', 'R', 'C'],
                            [$task->length ?? 0, $task->width ?? 0, $task->height ?? 0],
                            $mathString
                        );

                        if (preg_match('/^[0-9\.\+\-\*\/\(\)\s]+$/', $mathString)) {
                            try {
                                $volume_per_item = eval("return $mathString;");
                                $count = $task->item_count ?? 1;
                                $task->quantity = round($volume_per_item * $count, 5);
                                $task->unit = $formula->unit;
                            } catch (\Throwable $th) {
                                $task->quantity = 0;
                            }
                        }
                    }
                } else {
                    $task->quantity = null;
                    $task->unit = null;
                }

                // 2. TÍNH TIỀN THÙ LAO (SỐ LƯỢNG * ĐƠN GIÁ)
                if (!empty($task->stone_price_id) && $task->quantity > 0) {
                    $stone = StonePrice::find($task->stone_price_id);
                    if ($stone) {
                        $calculatedReward = $task->quantity * $stone->price_per;
                        $task->reward = round($calculatedReward, 0);
                    }
                }
            }

            if ($task->isDirty('completed_count')) {
                if ($task->completed_count >= $task->item_count) {
                    $task->status = 'pending_review';
                }
            }
        });

        static::updating(function ($task) {            
            // [KHIÊN BẢO VỆ ĐẶC BIỆT]: KHÓA QUYỀN CỦA ROLE GIÁM SÁT
            $user = auth()->user();
            $isGiamSat = false;

            if ($user && is_object($user->role) && isset($user->role->name) && $user->role->name === 'giam_sat') {
                $isGiamSat = true;
            }

            if ($isGiamSat) {
                $dirty = $task->getDirty();
                $allowedFields = ['status', 'reject_reason', 'updated_at'];
                $illegalChanges = [];

                foreach ($dirty as $field => $newValue) {
                    if (!in_array($field, $allowedFields)) {
                        $oldValue = $task->getOriginal($field);
                        
                        if (empty($oldValue) && empty($newValue)) continue;
                        if (is_numeric($oldValue) && is_numeric($newValue) && (float)$oldValue === (float)$newValue) continue;
                        if ((string)$oldValue === (string)$newValue) continue;

                        $illegalChanges[] = $field;
                    }
                }

                if (count($illegalChanges) > 0) {
                    $errorMsg = "Quyền Giám sát chỉ được phép thay đổi Trạng thái và Lý do không đạt! (Đã chặn sửa trường: " . implode(', ', $illegalChanges) . ")";

                    // if (request()->expectsJson() || request()->is('api/*')) {
                    //     throw new \Exception($errorMsg, 400);
                    // }

                    throw new \Illuminate\Http\Exceptions\HttpResponseException(
                        redirect()->back()->with([
                            'message'    => $errorMsg,
                            'alert-type' => 'error',
                        ])
                    );
                }
            }
            // [KHIÊN BẢO VỆ 2]: NGĂN CHẶN SỬA THÔNG TIN KHÁC NẾU ĐÃ HOÀN THÀNH
            if ($task->getOriginal('status') === 'completed') {

                
                if (!empty($task->getOriginal('monthly_payroll_id')) && $task->isDirty('status')) {
                    
                    if ($task->getOriginal('status') !== $task->status) {
                        $errorMsg = "Không thể đổi trạng thái! Công việc này đã được chốt vào Phiếu ứng lương. Vui lòng xóa phiếu lương (hoặc đưa phiếu về chờ duyệt) trước.";

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

                
                $dirty = $task->getDirty();
                $realChanges = [];

                foreach ($dirty as $field => $newValue) {
                    
                    if (in_array($field, ['status', 'updated_at', 'monthly_payroll_id'])) {
                        continue;
                    }

                    $oldValue = $task->getOriginal($field);

                    
                    
                    
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
                    $errorMsg = "Công việc đã hoàn thành! KHÔNG được sửa các thông tin khác (Trường bị chặn: " . implode(', ', $realChanges) . "). Nếu cần sửa vui lòng chỉnh trạng thái công việc về chờ nghiệm thu/đang thi công.";

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

            
            if ($task->isDirty('status') && $task->status === 'rejected') {
                DB::table('task_rejected_histories')->insert([
                    'task_id'          => $task->id,
                    'failed_worker_id' => $task->getOriginal('worker_id'),
                    'old_reward'       => $task->getOriginal('reward'),
                    'reason'           => $task->reject_reason,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            
            if ($task->isDirty('worker_id') && $task->worker_id != null) {
                $task->status = 'waiting';
            }
        });

        
        static::created(function ($task) {
            if ($task->order) {
                $task->order->updateStats();
            }
        });

        static::updated(function ($task) {
            if ($task->isDirty('status')) {
                if ($task->order) {
                    $task->order->updateStats();
                }
            }

            // BƠM TIỀN VÀO KÉT KHI DUYỆT COMPLETED
            if ($task->isDirty('status') && $task->status === 'completed' && $task->reward > 0) {
                if ($task->worker) {
                    $task->worker->total_balance += $task->reward;
                    $task->worker->save();
                }
            }

            // RÚT LẠI TIỀN NẾU SẾP HỦY COMPLETED (Chuyển về trạng thái khác)
            if ($task->isDirty('status') && $task->getOriginal('status') === 'completed' && $task->status !== 'completed') {
                if ($task->worker) {
                    $task->worker->total_balance -= $task->getOriginal('reward');
                    $task->worker->save();
                }
            }
        });
    }

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function worker()
    {
        return $this->belongsTo(NhanVien::class, 'worker_id');
    }

    public function monthlyPayroll()
    {
        return $this->belongsTo(MonthlyPayroll::class, 'monthly_payroll_id');
    }

    public function stonePrice()
    {
        return $this->belongsTo(StonePrice::class, 'stone_price_id');
    }

    public function designer()
    {
        return $this->belongsTo(NhanVien::class, 'designer_id');
    }
}
