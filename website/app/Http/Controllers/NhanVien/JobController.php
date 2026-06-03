<?php

namespace App\Http\Controllers\NhanVien;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class JobController extends Controller
{

    public function newJobs(Request $request)
    {
        $user = auth('nhan_vien')->user();

        if ($user->role == 1) {
            return redirect()->route('nhanvien.jobs.active')->with('error', 'Nhân viên thiết kế thực hiện công việc trực tiếp tại danh sách đang làm.');
        }

        $search = $request->input('search');

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

        $activeItems = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $viewType = 'task';

        return view('nhanvien.jobs.new', compact('activeItems', 'viewType'));
    }


    public function showJob($type, $id)
    {
        $user = auth('nhan_vien')->user();

        if ($type == 'order') {
            if ($user->role == 1) {
                $item = Order::where('id', $id)->where('designer_id', $user->id)->firstOrFail();
            } else {
                abort(403, 'Bạn không có quyền xem dữ liệu này');
            }
        } elseif ($type == 'task') {
            if ($user->role == 1) {
                $item = Task::with('order')->where('id', $id)->where('designer_id', $user->id)->firstOrFail();
            } elseif ($user->role == 2) {
                $item = Task::with('order')->where('id', $id)->where('worker_id', $user->id)->firstOrFail();
            } else {
                abort(403, 'Bạn không có quyền xem dữ liệu này');
            }
        } else {
            abort(404);
        }

        return view('nhanvien.jobs.show', compact('item', 'type', 'user'));
    }


    public function acceptJob($type, $id)
    {
        $user = auth('nhan_vien')->user();
        if ($type == 'task' && $user->role == 2) {
            $item = Task::where('id', $id)->where('worker_id', $user->id)->firstOrFail();

            if ($item->status == 'waiting') {
                $item->status = 'processing';
                $item->save();

                return redirect()->route('nhanvien.jobs.active')
                    ->with('success', 'Đã nhận công việc thành công!');
            }
        }

        return redirect()->back()->with('error', 'Không thể nhận việc hoặc công việc không còn ở trạng thái chờ!');
    }

    public function rejectJob($type, $id)
    {
        $user = auth('nhan_vien')->user();
        if ($type == 'task' && $user->role == 2) {
            $item = Task::where('id', $id)->where('worker_id', $user->id)->firstOrFail();

            if ($item->status == 'waiting') {
                $item->status = 'rejected_by_worker';
                $item->save();

                return redirect()->route('nhanvien.jobs.new')
                    ->with('success', 'Bạn đã từ chối nhận công việc này.');
            }
        }

        return redirect()->back()->with('error', 'Không thể từ chối hoặc công việc không còn ở trạng thái chờ!');
    }


    public function activeJobs(Request $request)
    {
        $user = auth('nhan_vien')->user();
        $search = $request->input('search');

        if ($user->role == 1) {
            $ordersQuery = Order::where('designer_id', $user->id)
                ->where(function ($query) {
                    $query->where('status', '!=', 'completed')
                        ->orWhereNull('design_image')
                        ->orWhereNull('quy_cach');
                });

            $tasksQuery = Task::with('order')
                ->where('designer_id', $user->id)
                ->where(function ($query) {
                    $query->where('status', '!=', 'completed')
                        ->orWhereNull('task_files');
                });

            if ($search) {
                $ordersQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('id', $search);
                });

                $tasksQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('id', $search)
                        ->orWhereHas('order', function ($orderQ) use ($search) {
                            $orderQ->where('name', 'like', '%' . $search . '%');
                        });
                });
            }
            $activeOrders = $ordersQuery->orderBy('updated_at', 'desc')->paginate(10, ['*'], 'orders_page')->withQueryString();
            $activeTasks = $tasksQuery->orderBy('updated_at', 'desc')->paginate(10, ['*'], 'tasks_page')->withQueryString();

            return view('nhanvien.jobs.active', compact('activeOrders', 'activeTasks'));
        } else {
            $query = Task::with('order')
                ->where('worker_id', $user->id)
                ->whereIn('status', ['processing', 'pending_review']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('id', $search);
                });
            }

            $activeItems = $query->orderByRaw("FIELD(status, 'processing', 'pending_review')")
                ->orderBy('deadline', 'asc')
                ->paginate(10)->withQueryString();

            $viewType = 'task';

            return view('nhanvien.jobs.active', compact('activeItems', 'viewType'));
        }
    }


    public function submitJob(Request $request, $type, $id)
    {
        $user = auth('nhan_vien')->user();


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

            foreach ($files as $file) {
                $originalName = $file->getClientOriginalName();
                $safeName = \Illuminate\Support\Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs($folder, $safeName, 'public');

                $existingFiles[] = [
                    'download_link' => $path,
                    'original_name' => $originalName
                ];
            }

            return json_encode($existingFiles, JSON_UNESCAPED_UNICODE);
        };


        if ($user->role == 1) {


            if ($type == 'order') {
                $item = Order::where('id', $id)->where('designer_id', $user->id)->firstOrFail();

                if ($item->status === 'completed') {
                    return redirect()->back()->with('error', 'Đơn hàng này đã hoàn thành, bạn không thể thay đổi tài liệu nữa!');
                }

                if (!$request->hasFile('design_image') && !$request->hasFile('quy_cach')) {
                    return redirect()->back()->with('error', 'Vui lòng chọn ít nhất 1 file (Bản vẽ hoặc Quy cách) để cập nhật!');
                }

                $request->validate([
                    'design_image.*' => 'nullable|file|max:10240',
                    'quy_cach.*'     => 'nullable|file|max:10240',
                ], [
                    'design_image.*.max' => 'Dung lượng mỗi file Bản vẽ không được vượt quá 10MB.',
                    'quy_cach.*.max'     => 'Dung lượng mỗi file Quy cách không được vượt quá 10MB.',
                ]);

                if ($request->hasFile('design_image')) {
                    $item->design_image = $processMultipleFiles('design_image', $item->design_image, 'orders');
                }

                if ($request->hasFile('quy_cach')) {
                    $item->quy_cach = $processMultipleFiles('quy_cach', $item->quy_cach, 'orders');
                }

                $item->save();

                return redirect()->back()->with('success', 'Đã lưu tài liệu Đơn hàng thành công!');
            }

            if ($type == 'task') {
                $item = Task::where('id', $id)->where('designer_id', $user->id)->firstOrFail();

                if ($item->status === 'completed') {
                    return redirect()->back()->with('error', 'Công việc này đã hoàn thành, không thể thay đổi tài liệu!');
                }

                if (!$request->hasFile('task_files')) {
                    return redirect()->back()->with('error', 'Vui lòng chọn file bản vẽ chi tiết để tải lên!');
                }

                $request->validate([
                    'task_files.*' => 'required|file|max:10240',
                ], [
                    'task_files.*.required' => 'Vui lòng đính kèm file.',
                    'task_files.*.max' => 'Dung lượng mỗi file không được vượt quá 10MB.',
                ]);

                $item->task_files = $processMultipleFiles('task_files', $item->task_files, 'tasks');
                $item->save();

                return redirect()->back()->with('success', 'Đã tải lên Bản vẽ chi tiết thành công!');
            }
        } elseif ($type == 'task' && $user->role == 2) {
            $item = Task::where('id', $id)->where('worker_id', $user->id)->firstOrFail();

            $item->status = 'pending_review';
            $item->save();

            return redirect()->route('nhanvien.jobs.active')
                ->with('success', 'Đã gửi yêu cầu nghiệm thu cho Quản lý!');
        }
        return redirect()->back()->with('error', 'Không thể thao tác hoặc bạn không có quyền!');
    }


    public function completedJobs(Request $request)
    {
        $user = auth('nhan_vien')->user();
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
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('id', $search);
                });

                $tasksQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('id', $search)
                        ->orWhereHas('order', function ($orderQ) use ($search) {
                            $orderQ->where('name', 'like', '%' . $search . '%');
                        });
                });
            }
            $completedOrders = $ordersQuery->orderBy('updated_at', 'desc')->paginate(12, ['*'], 'orders_page')->withQueryString();
            $completedTasks = $tasksQuery->orderBy('updated_at', 'desc')->paginate(12, ['*'], 'tasks_page')->withQueryString();

            return view('nhanvien.jobs.completed', compact('completedOrders', 'completedTasks'));
        } else {
            $baseQuery = Task::with('order')
                ->where('worker_id', $user->id)
                ->where('status', 'completed')
                ->orderBy('updated_at', 'desc');

            if ($search) {
                $baseQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('id', $search);
                });
            }

            $completedItems = $baseQuery->paginate(12)->withQueryString();
            $viewType = 'task';

            return view('nhanvien.jobs.completed', compact('completedItems', 'viewType'));
        }
    }

    public function updateProgress(Request $request, $type, $id)
    {
        $user = auth('nhan_vien')->user();
        if ($type == 'task' && $user->role == 2) {
            $item = Task::where('id', $id)->where('worker_id', $user->id)->firstOrFail();

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
                $message = 'Đã hoàn thành 100% tiến độ! Hệ thống đã tự động gửi yêu cầu nghiệm thu cho Quản lý.';
            } else {
                $message = 'Đã cập nhật thêm ' . $request->add_count . ' sản phẩm thành công!';
            }

            return redirect()->back()->with('success', $message);
        }

        return redirect()->back()->with('error', 'Không thể thao tác!');
    }
}
