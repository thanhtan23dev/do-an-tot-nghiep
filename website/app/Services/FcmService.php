<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Illuminate\Support\Facades\Log;

class FcmService
{
    public static function send($token, $title, $body, $data = [])
    {
        if (!$token) return false;

        try {
            $firebase = (new Factory)
                ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));
            
            $messaging = $firebase->createMessaging();

            
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body))
                ->withData($data)
                ->withAndroidConfig(AndroidConfig::fromArray([
                    'priority' => 'high', 
                    'notification' => [
                        'default_sound' => true,
                        'default_vibrate_timings' => true, 
                    ],
                ]));

            $messaging->send($message);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Lỗi gửi thông báo FCM: ' . $e->getMessage());
            return false;
        }
    }
}