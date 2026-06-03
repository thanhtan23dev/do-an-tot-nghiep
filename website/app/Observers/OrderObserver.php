<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\NhanVien;
use App\Services\FcmService;

class OrderObserver
{
    public function created(Order $order)
    {
        if ($order->designer_id) {
            $designer = NhanVien::find($order->designer_id);

            if ($designer && $designer->fcm_token) {
                FcmService::send(
                    $designer->fcm_token,
                    '🔥 Bạn vừa được giao công việc thiết kế mới!',
                    'Đơn hàng: ' . $order->name,
                    [
                        'type' => 'new_order',
                        'order_id' => (string)$order->id
                    ]
                );
            }
        }
    }

    public function updated(Order $order)
    {
        if ($order->isDirty('designer_id') && $order->designer_id != null) {
            $designer = NhanVien::find($order->designer_id);

            if ($designer && $designer->fcm_token) {
                FcmService::send(
                    $designer->fcm_token,
                    '🔥 Bạn vừa được giao công việc thiết kế mới!',
                    'Đơn hàng: ' . $order->name,
                    [
                        'type' => 'new_order',
                        'order_id' => (string)$order->id
                    ]
                );
            }
        }
    }
}