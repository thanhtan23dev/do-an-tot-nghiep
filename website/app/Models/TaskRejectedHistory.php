<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskRejectedHistory extends Model
{
    // Chỉ định rõ tên bảng nếu Laravel không tự đoán đúng
    protected $table = 'task_rejected_histories';

    protected $fillable = [
        'task_id',
        'failed_worker_id',
        'old_reward',
        'reason',
    ];

    /**
     * Mối quan hệ: Lịch sử này thuộc về Công việc nào?
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * Mối quan hệ: Ai là người thợ đã làm hỏng/không đạt?
     */
    public function worker()
    {
        return $this->belongsTo(NhanVien::class, 'failed_worker_id');
    }
}
