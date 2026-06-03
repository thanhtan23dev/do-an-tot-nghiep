<?php

namespace App\Http\Controllers\Voyager;

use TCG\Voyager\Http\Controllers\VoyagerController as BaseVoyagerController;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Order;
use Carbon\Carbon;
use App\Services\GeminiAiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\MonthlyPayroll;

class VoyagerController extends BaseVoyagerController
{
    public function index()
    {
        $now = Carbon::now();
        $in48Hours = $now->copy()->addHours(48);

        $overdueTasks = Task::with('worker')
            ->whereIn('status', ['processing'])
            ->whereNotNull('deadline')
            ->where('deadline', '<', $now)
            ->orderBy('deadline', 'asc')
            ->get();

        $overdueOrders = Order::with('designer')
            ->whereIn('status', ['processing', 'waiting'])
            ->whereNotNull('deadline')
            ->whereNull('design_image')
            ->where('deadline', '<', $now)
            ->orderBy('deadline', 'asc')
            ->get();

        $totalOverdue = $overdueTasks->count() + $overdueOrders->count();

        $upcomingTasks = Task::with('worker')
            ->whereIn('status', ['processing'])
            ->whereNotNull('deadline')
            ->whereBetween('deadline', [$now, $in48Hours])
            ->orderBy('deadline', 'asc')
            ->get();

        $upcomingOrders = Order::with('designer')
            ->whereIn('status', ['processing'])
            ->whereNotNull('deadline')
            ->whereBetween('deadline', [$now, $in48Hours])
            ->orderBy('deadline', 'asc')
            ->get();

        $totalUpcoming = $upcomingTasks->count() + $upcomingOrders->count();


        $pendingTasks = Task::with('worker')
            ->where('status', 'pending_review')
            ->orderBy('updated_at', 'desc')
            ->get();

        $totalPending = $pendingTasks->count();

        $pendingPayrolls = MonthlyPayroll::with('worker')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $totalPendingPayrolls = $pendingPayrolls->count();


        $thisMonthOrders = Order::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();


        $thisMonthCompletedOrders = Order::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->where('status', 'completed')
            ->count();




        $activeOrders = Order::with(['tasks' => function ($query) {
            $query->where('status', '!=', 'completed');
        }])
            ->whereIn('status', ['processing', 'waiting'])
            ->whereNotNull('deadline')
            ->where('total_tasks', '>', 0)
            ->get();

        foreach ($activeOrders as $order) {
            $currentNow = \Carbon\Carbon::now();
            $createdAt = \Carbon\Carbon::parse($order->created_at);
            $deadline = \Carbon\Carbon::parse($order->deadline);

            $totalMinutes = $createdAt->diffInMinutes($deadline);
            $passedMinutes = $createdAt->diffInMinutes($currentNow);

            if ($totalMinutes <= 0) $totalMinutes = 1;

            $percentPlanned = ($passedMinutes / $totalMinutes) * 100;
            if ($percentPlanned > 100) $percentPlanned = 100;
            if ($percentPlanned < 0) $percentPlanned = 0;

            $percentComplete = ($order->completed_tasks / $order->total_tasks) * 100;
            if ($percentComplete > 100) $percentComplete = 100;

            if ($percentPlanned == 0) {
                $spi = $percentComplete > 0 ? 1.5 : 1.0;
            } else {
                $spi = $percentComplete / $percentPlanned;
            }

            if ($currentNow->greaterThan($deadline) && $percentComplete < 100) {
                $status = 'Quá hạn';
                $barColor = 'progress-bar-danger';
                $labelColor = 'label-danger';
            } elseif ($percentComplete >= 100) {
                $status = 'hoanthanh';
                $barColor = 'progress-bar-success';
                $labelColor = 'label-success';
            } elseif ($spi < 0.95) {
                $status = 'Chậm tiến độ';
                $barColor = 'progress-bar-warning';
                $labelColor = 'label-warning';
            } elseif ($spi > 1.05) {
                $status = 'Vượt tiến độ';
                $barColor = 'progress-bar-info';
                $labelColor = 'label-info';
            } else {
                $status = 'Đúng tiến độ';
                $barColor = 'progress-bar-success';
                $labelColor = 'label-success';
            }

            $order->percent_planned = $percentPlanned;
            $order->percent_complete = $percentComplete;
            $order->spi = number_format($spi, 2);
            $order->spi_status = $status;
            $order->bar_color = $barColor;
            $order->label_color = $labelColor;


            $order->ai_muc_do = null;
            $order->ai_ly_do = null;
        }

        return view('vendor.voyager.index', compact(
            'overdueTasks',
            'overdueOrders',
            'totalOverdue',
            'upcomingTasks',
            'upcomingOrders',
            'totalUpcoming',
            'pendingTasks',
            'totalPending',
            'thisMonthOrders',
            'thisMonthCompletedOrders',
            'activeOrders',
            'pendingPayrolls',
            'totalPendingPayrolls'
        ));
    }


    public function fetchAiInsights()
    {

        $activeOrders = Order::with(['tasks' => function ($query) {
            $query->where('status', '!=', 'completed');
        }])
            ->whereIn('status', ['processing', 'waiting'])
            ->whereNotNull('deadline')
            ->where('total_tasks', '>', 0)
            ->get();


        $orderDataForAI = $activeOrders->map(function ($order) {
            return [
                'id' => $order->id,
                'ten_don_hang' => $order->name,
                'ngay_con_lai' => Carbon::now()->diffInDays($order->deadline, false),
                'cac_viec_chua_xong' => $order->tasks->map(function ($task) {
                    return [
                        'ten_viec' => $task->name,
                        'mo_ta' => $task->description,
                        'trang_thai_hien_tai' => $task->status,
                        'so_luong_can_lam' => $task->item_count,
                        'so_luong_da_xong' => $task->completed_count
                    ];
                })->values()->toArray(),
            ];
        })->toArray();

        if (empty($orderDataForAI)) {
            return response()->json([]);
        }

        try {
            // CÁCH 1: CACHE THEO SỰ THAY ĐỔI (REAL-TIME)

            // $dataHash = md5(json_encode($orderDataForAI));
            // $cacheKey = 'gemini_insights_' . $dataHash;
            // $aiInsights = Cache::remember($cacheKey, now()->addDays(1), function () use ($orderDataForAI) {
            //     return GeminiAiService::analyzeProgress($orderDataForAI);
            // });

            // CÁCH 2: CACHE CỐ ĐỊNH THEO THỜI GIAN (2 TIẾNG / LẦN)

            $cacheKey = 'gemini_insights_fixed_2hours';
            $aiInsights = Cache::get($cacheKey);

            if (empty($aiInsights)) {
                
                $aiInsights = GeminiAiService::analyzeProgress($orderDataForAI);

                if (!empty($aiInsights)) {
                    Cache::put($cacheKey, $aiInsights, now()->addHours(2));
                }
            }

            return response()->json($aiInsights);
        } catch (\Exception $e) {
            Log::error('Lỗi gọi API AI Async: ' . $e->getMessage());
            return response()->json(['error' => true], 500);
        }
    }
    public function markAsCompleted($id)
    {
    $order = Order::findOrFail($id);

    $order->status = 'completed'; 
    $order->save();
    return redirect()->back()->with([
        'message'    => "Đã xác nhận hoàn thành đơn hàng thành công!",
        'alert-type' => 'success',
    ]);
    }
}
