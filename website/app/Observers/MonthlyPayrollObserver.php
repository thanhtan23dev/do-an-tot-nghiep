<?php

namespace App\Observers;

use App\Models\MonthlyPayroll;
use App\Models\NhanVien;
use App\Services\FcmService;

class MonthlyPayrollObserver
{
    public function updated(MonthlyPayroll $payroll)
    {
        
        if ($payroll->isDirty('status')) {
            $user = NhanVien::find($payroll->nhan_vien_id);

            if ($user && $user->fcm_token) {
                $title = '';
                $body = '';

                
                if ($payroll->status == 'paid') {
                    $title = '💰 Yêu cầu tài chính đã được duyệt!';
                    $body = 'Kỳ lương tháng ' . $payroll->month . '/' . $payroll->year . ' của bạn đã được xử lý.';
                } 
                
                // elseif ($payroll->status == 'rejected') {
                //     $title = '❌ Yêu cầu ứng tiền bị từ chối';
                //     $body = 'Quản lý đã hủy yêu cầu của bạn. Xem chi tiết trong ứng dụng.';
                // }

                if ($title !== '') {
                    FcmService::send($user->fcm_token, $title, $body, [
                        'type' => 'salary_advance',
                        'id' => (string)$payroll->id
                    ]);
                }
            }
        }
    }
}