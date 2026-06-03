<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Order;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now();

        $stats = [
            'new_jobs'      => 0,
            'active_jobs'   => 0,
            'total_balance' => $user->total_balance ?? 0,
        ];

        if ($user->role == 1) { // 1. DÀNH CHO NHÂN VIÊN THIẾT KẾ

            $stats['new_jobs'] = Order::where('designer_id', $user->id)
                ->where('status', 'waiting')
                ->whereNull('design_image')
                ->count();

            $stats['active_jobs'] = Order::where('designer_id', $user->id)
                ->where('status', 'processing')
                ->whereNull('design_image')
                ->count();

            // Công việc sắp đến hạn (Dưới 48h)
            $nearDeadlines = Order::where('designer_id', $user->id)
                ->where('status', '!=', 'completed')
                ->whereNull('design_image')
                ->whereNotNull('deadline')
                ->where('deadline', '<=', $now->copy()->addHours(48))
                ->orderBy('deadline', 'asc')
                ->get();

            $type = 'order';
        } else { // 2. DÀNH CHO THỢ THI CÔNG

            $stats['new_jobs'] = Task::where('worker_id', $user->id)
                ->where('status', 'waiting')
                ->count();

            $stats['active_jobs'] = Task::where('worker_id', $user->id)
                ->where('status', 'processing')
                ->count();

            // Công việc sắp đến hạn (Dưới 48h)
            $nearDeadlines = Task::with('order')
                ->where('worker_id', $user->id)
                ->where('status', 'processing')
                ->whereNotNull('deadline')
                ->where('deadline', '<=', $now->copy()->addHours(48))
                ->orderBy('deadline', 'asc')
                ->get();

            $type = 'task';
        }

        return response()->json([
            'success'     => true,
            'type'        => $type,
            'is_designer' => $user->role == 1,
            'data'        => [
                'stats'          => $stats,
                'near_deadlines' => $nearDeadlines,
            ]
        ], 200);
    }
}
