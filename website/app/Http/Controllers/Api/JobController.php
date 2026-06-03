<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobController extends Controller
{

    public function newJobs(Request $request)
    {
        $user = $request->user();
        $search = $request->input('search');

        if ($user->role == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Nhân viên thiết kế thực hiện công việc trực tiếp tại màn hình Đang làm.'
            ], 400);
        }

        $query = Task::with('order')
            ->where('worker_id', $user->id)
            ->where('status', 'waiting');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('id', $search)
                    ->orWhereHas('order', function ($orderQ) use ($search) {
                        $orderQ->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $data = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json(['success' => true, 'type' => 'task', 'data' => $data], 200);
    }


    public function activeJobs(Request $request)
    {
        $user = $request->user();
        $search = $request->input('search');

        if ($user->role == 1) {


            $ordersQuery = Order::where('designer_id', $user->id)
                ->where('status', '!=', 'completed')
                ->where(function ($query) {
                    $query->whereNull('design_image')
                        ->orWhere('design_image', '[]')
                        ->orWhereNull('quy_cach')
                        ->orWhere('quy_cach', '[]');
                });


            $tasksQuery = Task::with('order')
                ->where('designer_id', $user->id)
                ->where('status', '!=', 'completed')
                ->where(function ($query) {
                    $query->whereNull('task_files')
                        ->orWhere('task_files', '[]');
                });

            if ($search) {
                $ordersQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')->orWhere('id', $search);
                });

                $tasksQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('id', $search)
                        ->orWhereHas('order', function ($orderQ) use ($search) {
                            $orderQ->where('name', 'like', '%' . $search . '%');
                        });
                });
            }


            $ordersData = $ordersQuery->orderBy('updated_at', 'desc')->paginate(10, ['*'], 'orders_page');
            $tasksData = $tasksQuery->orderBy('updated_at', 'desc')->paginate(10, ['*'], 'tasks_page');

            return response()->json([
                'success' => true,
                'is_designer' => true,
                'data' => [
                    'orders' => $ordersData,
                    'tasks' => $tasksData
                ]
            ], 200);
        } else {

            $query = Task::with('order')
                ->where('worker_id', $user->id)
                ->whereIn('status', ['processing', 'pending_review']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('id', $search)
                        ->orWhereHas('order', function ($orderQ) use ($search) {
                            $orderQ->where('name', 'like', '%' . $search . '%');
                        });
                });
            }

            $data = $query->orderByRaw("FIELD(status, 'processing', 'pending_review')")
                ->orderBy('deadline', 'asc')->paginate(10);

            return response()->json([
                'success' => true,
                'is_designer' => false,
                'type' => 'task',
                'data' => $data
            ], 200);
        }
    }


    public function showJob(Request $request, $type, $id)
    {
        $user = $request->user();

        if ($type == 'order' && $user->role == 1) {
            $item = Order::where('id', $id)->where('designer_id', $user->id)->first();
        } elseif ($type == 'task') {
            if ($user->role == 1) {
                $item = Task::with('order')->where('id', $id)->where('designer_id', $user->id)->first();
            } else {
                $item = Task::with('order')->where('id', $id)->where('worker_id', $user->id)->first();
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Hành động không hợp lệ'], 400);
        }

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy hoặc bạn không có quyền truy cập dữ liệu này'], 404);
        }


        if ($type == 'order') {
            if ($item->design_image == '[]') $item->design_image = null;
            if ($item->quy_cach == '[]') $item->quy_cach = null;
        } else {
            if ($item->task_files == '[]') $item->task_files = null;
        }

        return response()->json(['success' => true, 'data' => $item], 200);
    }


    public function acceptJob(Request $request, $type, $id)
    {
        $user = $request->user();

        if ($type == 'task' && $user->role == 2) {
            $item = Task::where('id', $id)->where('worker_id', $user->id)->first();

            if ($item && $item->status == 'waiting') {
                $item->update(['status' => 'processing']);
                return response()->json(['success' => true, 'message' => 'Đã nhận công việc thành công!']);
            }
        }

        return response()->json(['success' => false, 'message' => 'Không thể nhận việc hoặc công việc không còn ở trạng thái chờ!'], 400);
    }


    public function rejectJob(Request $request, $type, $id)
    {
        $user = $request->user();

        if ($type == 'task' && $user->role == 2) {
            $item = Task::where('id', $id)->where('worker_id', $user->id)->first();

            if ($item && $item->status == 'waiting') {
                $item->update(['status' => 'rejected_by_worker']);
                return response()->json(['success' => true, 'message' => 'Bạn đã từ chối nhận công việc này.']);
            }
        }

        return response()->json(['success' => false, 'message' => 'Không thể từ chối hoặc công việc không còn ở trạng thái chờ!'], 400);
    }


    public function submitJob(Request $request, $type, $id)
    {
        $user = $request->user();


        $processMultipleFiles = function ($fileInputName, $currentDbValue, $folderName) use ($request) {
            $files = $request->file($fileInputName);
            if (!$files) return $currentDbValue;

            $existingFiles = [];
            if (!empty($currentDbValue) && $currentDbValue !== '[]') {
                $decoded = json_decode($currentDbValue, true);
                if (is_array($decoded)) {
                    $existingFiles = $decoded;
                } else {
                    $existingFiles = [['download_link' => $currentDbValue, 'original_name' => 'old_file']];
                }
            }

            $folder = $folderName . '/' . date('F') . date('Y');


            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                $originalName = $file->getClientOriginalName();
                $safeName = \Illuminate\Support\Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . $file->getClientOriginalExtension();

                $path = $file->storeAs($folder, $safeName, 'public');

                $existingFiles[] = [
                    'download_link' => str_replace('\\', '/', $path),
                    'original_name' => $originalName
                ];
            }

            return json_encode($existingFiles, JSON_UNESCAPED_UNICODE);
        };


        if ($user->role == 1) {


            if ($type == 'order') {
                $item = Order::where('id', $id)->where('designer_id', $user->id)->first();
                if (!$item) return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);

                if ($item->status === 'completed') {
                    return response()->json(['success' => false, 'message' => 'Đơn hàng đã hoàn thành, không thể sửa tài liệu'], 400);
                }

                if (!$request->hasFile('design_image') && !$request->hasFile('quy_cach')) {
                    return response()->json(['success' => false, 'message' => 'Vui lòng đính kèm ít nhất 1 file (Bản vẽ hoặc Quy cách)'], 400);
                }

                $request->validate([
                    'design_image.*' => 'nullable|file|max:10240',
                    'quy_cach.*'     => 'nullable|file|max:10240',
                ]);

                if ($request->hasFile('design_image')) {
                    $item->design_image = $processMultipleFiles('design_image', $item->design_image, 'orders');
                }

                if ($request->hasFile('quy_cach')) {
                    $item->quy_cach = $processMultipleFiles('quy_cach', $item->quy_cach, 'orders');
                }

                $item->save();
                return response()->json(['success' => true, 'message' => 'Đã lưu tài liệu Đơn hàng thành công!']);
            }


            if ($type == 'task') {
                $item = Task::where('id', $id)->where('designer_id', $user->id)->first();
                if (!$item) return response()->json(['success' => false, 'message' => 'Không tìm thấy công việc'], 404);

                if ($item->status === 'completed') {
                    return response()->json(['success' => false, 'message' => 'Công việc đã hoàn thành, không thể sửa tài liệu'], 400);
                }

                if (!$request->hasFile('task_files')) {
                    return response()->json(['success' => false, 'message' => 'Vui lòng đính kèm file bản vẽ chi tiết'], 400);
                }

                $request->validate([
                    'task_files.*' => 'required|file|max:10240',
                ]);

                $item->task_files = $processMultipleFiles('task_files', $item->task_files, 'tasks');
                $item->save();

                return response()->json(['success' => true, 'message' => 'Đã lưu Bản vẽ chi tiết thành công!']);
            }
        } elseif ($type == 'task' && $user->role == 2) {
            $item = Task::where('id', $id)->where('worker_id', $user->id)->first();
            if (!$item) return response()->json(['success' => false, 'message' => 'Không tìm thấy công việc'], 404);

            $item->update(['status' => 'pending_review']);
            return response()->json(['success' => true, 'message' => 'Đã gửi yêu cầu nghiệm thu cho Quản lý!']);
        }

        return response()->json(['success' => false, 'message' => 'Hành động không hợp lệ'], 400);
    }


    public function updateProgress(Request $request, $type, $id)
    {
        $user = $request->user();

        if ($type == 'task' && $user->role == 2) {
            $item = Task::where('id', $id)->where('worker_id', $user->id)->first();
            if (!$item) return response()->json(['success' => false, 'message' => 'Không tìm thấy công việc'], 404);

            $request->validate([
                'add_count' => 'required|integer|min:1',
            ]);

            $newCount = $item->completed_count + $request->add_count;

            if ($newCount >= $item->item_count) {
                $newCount = $item->item_count;
            }

            $item->completed_count = $newCount;
            $item->save();

            if ($item->status == 'pending_review') {
                $message = 'Đã hoàn thành 100% tiến độ! Hệ thống đã tự động gửi yêu cầu nghiệm thu.';
            } else {
                $message = 'Đã cập nhật thêm ' . $request->add_count . ' sản phẩm thành công!';
            }

            return response()->json(['success' => true, 'message' => $message], 200);
        }

        return response()->json(['success' => false, 'message' => 'Không thể thao tác'], 400);
    }


    public function completedJobs(Request $request)
    {
        $user = $request->user();
        $search = $request->input('search');

        if ($user->role == 1) {

            $ordersQuery = Order::where('designer_id', $user->id)
                ->where('status', 'completed')
                ->where(function ($q) {
                    $q->where(function ($sub1) {
                        $sub1->whereNotNull('design_image')->where('design_image', '!=', '[]');
                    })->orWhere(function ($sub2) {
                        $sub2->whereNotNull('quy_cach')->where('quy_cach', '!=', '[]');
                    });
                });

            $tasksQuery = Task::with('order')
                ->where('designer_id', $user->id)
                ->where('status', 'completed')
                ->whereNotNull('task_files')
                ->where('task_files', '!=', '[]');

            if ($search) {
                $ordersQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')->orWhere('id', $search);
                });

                $tasksQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('id', $search)
                        ->orWhereHas('order', function ($orderQ) use ($search) {
                            $orderQ->where('name', 'like', '%' . $search . '%');
                        });
                });
            }

            $completedOrders = $ordersQuery->orderBy('updated_at', 'desc')->paginate(10, ['*'], 'orders_page');
            $completedTasks = $tasksQuery->orderBy('updated_at', 'desc')->paginate(10, ['*'], 'tasks_page');

            return response()->json([
                'success' => true,
                'is_designer' => true,
                'data' => [
                    'orders' => $completedOrders,
                    'tasks' => $completedTasks,
                ]
            ], 200);
        } else {

            $baseQuery = Task::with('order')
                ->where('worker_id', $user->id)
                ->where('status', 'completed')
                ->orderBy('updated_at', 'desc');

            if ($search) {
                $baseQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')->orWhere('id', $search);
                });
            }


            $completedItems = $baseQuery->paginate(12);

            return response()->json([
                'success' => true,
                'is_designer' => false,
                'type' => 'task',
                'data' => $completedItems
            ], 200);
        }
    }
}
