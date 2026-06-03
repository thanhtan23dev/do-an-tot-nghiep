<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\NhanVien;
use App\Services\FcmService;

class TaskObserver
{
    public function created(Task $task)
    {
       
        if ($task->worker_id) {
            $this->sendNotification(
                $task->worker_id, 
                '🔨 Quản lý vừa giao việc mới!', 
                'Công việc: ' . $task->name, 
                'new_task', 
                $task->id
            );
        }

       
        if ($task->designer_id) {
            $this->sendNotification(
                $task->designer_id, 
                '🔥 Bạn được giao thiết kế chi tiết mới!', 
                'Công việc: ' . $task->name, 
                'new_task', 
                $task->id
            );
        }
    }

    public function updated(Task $task)
    {
       
        
       
        if ($task->isDirty('worker_id') && $task->worker_id != null) {
            $this->sendNotification(
                $task->worker_id, 
                '🔨 Quản lý vừa giao việc mới!', 
                'Công việc: ' . $task->name, 
                'new_task', 
                $task->id
            );
        }

       
        if ($task->isDirty('designer_id') && $task->designer_id != null) {
            $this->sendNotification(
                $task->designer_id, 
                '🔥 Bạn được giao thiết kế chi tiết mới!', 
                'Công việc: ' . $task->name, 
                'new_task', 
                $task->id
            );
        }

        if ($task->isDirty('status') && $task->status === 'rejected') {
            
            $reason = 'Lý do: ' . ($task->reject_reason ?? 'Không có lý do cụ thể');

           
            if ($task->worker_id) {
                $this->sendNotification(
                    $task->worker_id, 
                    '⚠️ Nghiệm thu công việc thất bại!', 
                    $reason, 
                    'task_rejected', 
                    $task->id
                );
            }
        }
    }

    private function sendNotification($userId, $title, $body, $type, $taskId)
    {
        $user = NhanVien::find($userId);

        if ($user && $user->fcm_token) {
            FcmService::send(
                $user->fcm_token,
                $title,
                $body,
                [
                    'type' => $type,
                    'task_id' => (string)$taskId
                ]
            );
        }
    }
}