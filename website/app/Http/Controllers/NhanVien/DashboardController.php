<?php

namespace App\Http\Controllers\NhanVien;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Order;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth('nhan_vien')->user();
        $now = Carbon::now();

        $stats = [
            'new_jobs'      => 0,
            'active_jobs'   => 0,
            'total_balance' => $user->total_balance ?? 0,
        ];

        if ($user->role == 1) {
            $stats['new_jobs'] = Order::where('designer_id', $user->id)
                ->where('status', 'waiting')
                ->whereNull('design_image')
                ->count();

            $stats['active_jobs']   = Order::where('designer_id', $user->id)
                ->where('status', 'processing')
                ->whereNull('design_image')
                ->count();

            $nearDeadlines = Order::where('designer_id', $user->id)
                ->where('status', '!=', 'completed')
                ->whereNull('design_image')
                ->whereNotNull('deadline')
                ->where('deadline', '<=', $now->copy()->addHours(48))
                ->orderBy('deadline', 'asc')
                ->get();
        } else { // NHÂN CÔNG
            $stats['new_jobs']      = Task::where('worker_id', $user->id)->where('status', 'waiting')->count();
            $stats['active_jobs']   = Task::where('worker_id', $user->id)->where('status', 'processing')->count();

            $nearDeadlines = Task::with('order')
                ->where('worker_id', $user->id)
                ->where('status', 'processing')
                ->whereNotNull('deadline')
                ->where('deadline', '<=', $now->copy()->addHours(48))
                ->orderBy('deadline', 'asc')
                ->get();
        }

        return view('nhanvien.dashboard', compact('stats', 'nearDeadlines'));
    }
}
