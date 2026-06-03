@extends('layouts.app')

@section('title', 'Chi tiết công việc')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">

        <a href="{{ url()->previous() }}"
            class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Quay lại danh sách
        </a>

        <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 md:p-8 shadow-sm border border-gray-100 dark:border-gray-700">

            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $item->name }}</h1>
                <div class="flex items-center gap-3">
                    <span
                        class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                        @if ($user->role == 1)
                            {{ $type == 'order' ? 'Thiết kế tổng thể' : 'Thiết kế chi tiết' }}
                        @else
                            Thi công
                        @endif
                    </span>
                    <span class="text-sm font-medium text-gray-500 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Hạn:
                        {{ $item->deadline ? \Carbon\Carbon::parse($item->deadline)->format('d/m/Y H:i') : 'Chưa có hạn' }}
                    </span>
                </div>
            </div>

            <div class="mb-6 pb-6 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Mô tả công việc</h3>
                <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 text-sm">
                    {!! nl2br(e($item->description ?? 'Không có mô tả chi tiết.')) !!}
                </div>
            </div>

            @if ($type == 'task')
                <div class="mb-8">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Thông số & Tiến độ</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div
                            class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Kích thước (D-R-C)</p>
                            <p class="font-bold text-gray-800 dark:text-gray-200">
                                {{ $item->length ?? 0 }} x {{ $item->width ?? 0 }} x {{ $item->height ?? 0 }}
                            </p>
                        </div>
                        <div
                            class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-2xl border border-blue-100 dark:border-blue-800/30">
                            <p class="text-xs text-blue-600 dark:text-blue-400 mb-1">Số lượng</p>
                            <p class="font-bold text-blue-700 dark:text-blue-300 text-lg">
                                {{ $item->item_count ?? 1 }}
                            </p>
                        </div>
                        <div
                            class="bg-green-50 dark:bg-green-900/20 p-4 rounded-2xl border border-green-100 dark:border-green-800/30">
                            <p class="text-xs text-green-600 dark:text-green-400 mb-1">Đã hoàn thành</p>
                            <p class="font-bold text-green-700 dark:text-green-300 text-lg">
                                {{ $item->completed_count ?? 0 }} <span class="text-sm font-normal">/
                                    {{ $item->item_count ?? 1 }}</span>
                            </p>
                        </div>
                        <div
                            class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-2xl border border-purple-100 dark:border-purple-800/30">
                            <p class="text-xs text-purple-600 dark:text-purple-400 mb-1">Tổng khối lượng</p>
                            <p class="font-bold text-purple-700 dark:text-purple-300 text-lg">
                                {{ $item->quantity ?? 0 }} <span
                                    class="text-sm font-normal">{{ $item->unit ?? 'M3' }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="space-y-8">
                <div>
                    @if ($type == 'task')
                        @if ($item->reject_reason)
                            <div class="mb-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-5 rounded-r-2xl">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3
                                            class="text-sm font-bold text-red-800 dark:text-red-300 uppercase tracking-wider">
                                            Quản lý yêu cầu sửa</h3>
                                        <div class="mt-2 text-sm text-red-700 dark:text-red-400 font-medium">
                                            <p>{{ $item->reject_reason }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($user->role != 1)
                            <div
                                class="bg-blue-50 dark:bg-blue-900/10 p-6 rounded-2xl border border-blue-100 dark:border-blue-800/30 flex flex-col md:flex-row justify-between md:items-center gap-4">
                                <div>
                                    <p class="text-sm text-blue-600 dark:text-blue-400 font-medium mb-1">Thù lao thi công
                                        tổng cộng:</p>
                                    <p class="text-4xl font-black text-blue-700 dark:text-blue-300">
                                        {{ number_format($item->reward, 0, ',', '.') }} <span class="text-xl">VNĐ</span>
                                    </p>
                                </div>
                                <div class="hidden md:block opacity-20">
                                    <svg class="w-16 h-16 text-blue-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="bg-gray-50 dark:bg-gray-800/50 p-6 rounded-3xl border border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Tài liệu kỹ thuật
                    </h3>

                    @php
                        $rawDesign = $type == 'order' ? $item->design_image : $item->order->design_image ?? null;
                        $designFiles = [];
                        if ($rawDesign && $rawDesign !== '[]') {
                            $decoded = json_decode($rawDesign, true);
                            $designFiles = is_array($decoded)
                                ? $decoded
                                : [['download_link' => $rawDesign, 'original_name' => 'File_Thiet_Ke_Cu']];
                        }

                        $rawQuyCach = $type == 'order' ? $item->quy_cach : $item->order->quy_cach ?? null;
                        $quyCachFiles = [];
                        if ($rawQuyCach && $rawQuyCach !== '[]') {
                            $decoded = json_decode($rawQuyCach, true);
                            $quyCachFiles = is_array($decoded)
                                ? $decoded
                                : [['download_link' => $rawQuyCach, 'original_name' => 'File_Quy_Cach_Cu']];
                        }

                        $taskFilesList = [];
                        if ($type == 'task') {
                            $rawTaskFiles = $item->task_files;
                            if ($rawTaskFiles && $rawTaskFiles !== '[]') {
                                $decoded = json_decode($rawTaskFiles, true);
                                $taskFilesList = is_array($decoded)
                                    ? $decoded
                                    : [
                                        [
                                            'download_link' => $rawTaskFiles,
                                            'original_name' => 'Ban_ve_chi_tiet_cong_viec',
                                        ],
                                    ];
                            }
                        }
                    @endphp

                    <div class="mb-6 space-y-6">
                        @if (count($taskFilesList) > 0)
                            <div>
                                <h4
                                    class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 uppercase border-b border-gray-200 dark:border-gray-600 pb-2">
                                    Tài liệu chi tiết công việc này</h4>
                                <div class="space-y-3">
                                    @foreach ($taskFilesList as $index => $file)
                                        @php
                                            $link = $file['download_link'] ?? $file;
                                            $name = $file['original_name'] ?? 'Bản vẽ chi tiết ' . ($index + 1);
                                        @endphp
                                        <a href="{{ asset('storage/' . $link) }}" target="_blank"
                                            class="w-full flex items-center justify-between p-4 bg-white dark:bg-gray-900 border border-orange-200 dark:border-orange-900/30 rounded-2xl hover:shadow-md transition-shadow group">
                                            <div class="flex items-center gap-3 overflow-hidden">
                                                <div
                                                    class="p-2 bg-orange-100 dark:bg-orange-900/30 text-orange-600 rounded-lg flex-shrink-0">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                    </svg>
                                                </div>
                                                <div class="overflow-hidden">
                                                    <h4
                                                        class="text-sm font-bold text-gray-800 dark:text-white group-hover:text-orange-600 transition-colors truncate">
                                                        {{ $name }}</h4>
                                                    <p class="text-xs text-gray-500">Bấm để xem/tải bản vẽ thi công</p>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if (count($designFiles) > 0 || count($quyCachFiles) > 0)
                            <div>
                                <h4
                                    class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 uppercase border-b border-gray-200 dark:border-gray-600 pb-2">
                                    Tài liệu tổng quan đơn hàng</h4>
                                <div class="space-y-3">
                                    @foreach ($designFiles as $index => $file)
                                        @php
                                            $link = $file['download_link'] ?? $file;
                                            $name = $file['original_name'] ?? 'Bản thiết kế tổng ' . ($index + 1);
                                        @endphp
                                        <a href="{{ asset('storage/' . $link) }}" target="_blank"
                                            class="w-full flex items-center justify-between p-4 bg-white dark:bg-gray-900 border border-blue-200 dark:border-blue-900/30 rounded-2xl hover:shadow-md transition-shadow group">
                                            <div class="flex items-center gap-3 overflow-hidden">
                                                <div
                                                    class="p-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 rounded-lg flex-shrink-0">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                                <div class="overflow-hidden">
                                                    <h4
                                                        class="text-sm font-bold text-gray-800 dark:text-white group-hover:text-blue-600 transition-colors truncate">
                                                        {{ $name }}</h4>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach

                                    @foreach ($quyCachFiles as $index => $pdf)
                                        @php
                                            $link = $pdf['download_link'] ?? $pdf;
                                            $name = $pdf['original_name'] ?? 'Quy cách kỹ thuật ' . ($index + 1);
                                        @endphp
                                        <a href="{{ asset('storage/' . $link) }}" target="_blank"
                                            class="w-full flex items-center justify-between p-4 bg-white dark:bg-gray-900 border border-red-200 dark:border-red-900/30 rounded-2xl hover:shadow-md transition-shadow group">
                                            <div class="flex items-center gap-3 overflow-hidden">
                                                <div
                                                    class="p-2 bg-red-100 dark:bg-red-900/30 text-red-600 rounded-lg flex-shrink-0">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                                <div class="overflow-hidden">
                                                    <h4
                                                        class="text-sm font-bold text-gray-800 dark:text-white group-hover:text-red-600 transition-colors truncate">
                                                        {{ $name }}</h4>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    @if (count($designFiles) == 0 && count($quyCachFiles) == 0 && count($taskFilesList) == 0)
                        <div
                            class="w-full h-48 bg-white dark:bg-gray-900 rounded-2xl border border-dashed border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center text-gray-400 mb-6">
                            <svg class="w-10 h-10 mb-3 opacity-50" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            <span class="text-sm font-medium">Chưa có tài liệu kỹ thuật nào</span>
                        </div>
                    @endif

                    {{-- FORM UPLOAD CHO THIẾT KẾ --}}
                    @if ($user->role == 1 && $item->status != 'completed')
                        <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 uppercase">Cập nhật tài liệu
                                mới:</h4>
                            <form action="{{ route('nhanvien.job.submit', ['type' => $type, 'id' => $item->id]) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf

                                @if ($type == 'order')
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        {{-- Tải lên Bản vẽ --}}
                                        <div class="relative group/file">
                                            <label
                                                class="flex flex-col items-center justify-center w-full h-16 border-2 border-dashed border-blue-300 dark:border-gray-600 rounded-xl cursor-pointer bg-blue-50/50 hover:bg-blue-50 hover:border-blue-400 transition-all duration-300 px-2">
                                                <div class="flex items-center gap-3">
                                                    <svg class="w-6 h-6 text-blue-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                    </svg>
                                                    <div class="flex flex-col text-left overflow-hidden">
                                                        <span class="text-xs font-bold text-gray-600 uppercase">Tải lên Bản
                                                            vẽ</span>
                                                        <span id="img-name"
                                                            class="text-[10px] text-blue-600 font-medium truncate w-full">Định
                                                            dạng bất kỳ...</span>
                                                    </div>
                                                </div>
                                                <input type="file" name="design_image[]" multiple class="hidden"
                                                    onchange="displayFileName(this, 'img-name', 'blue')" />
                                            </label>
                                        </div>

                                        {{-- Tải lên Quy cách --}}
                                        <div class="relative group/file">
                                            <label
                                                class="flex flex-col items-center justify-center w-full h-16 border-2 border-dashed border-red-300 dark:border-gray-600 rounded-xl cursor-pointer bg-red-50/50 hover:bg-red-50 hover:border-red-400 transition-all duration-300 px-2">
                                                <div class="flex items-center gap-3">
                                                    <svg class="w-6 h-6 text-red-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <div class="flex flex-col text-left overflow-hidden">
                                                        <span class="text-xs font-bold text-gray-600 uppercase">Tải lên Quy
                                                            cách</span>
                                                        <span id="pdf-name"
                                                            class="text-[10px] text-red-600 font-medium truncate w-full">Định
                                                            dạng bất kỳ...</span>
                                                    </div>
                                                </div>
                                                <input type="file" name="quy_cach[]" multiple class="hidden"
                                                    onchange="displayFileName(this, 'pdf-name', 'red')" />
                                            </label>
                                        </div>
                                    </div>
                                @elseif($type == 'task')
                                    <div class="mb-4 relative group/file">
                                        <label
                                            class="flex flex-col items-center justify-center w-full h-16 border-2 border-dashed border-orange-300 dark:border-gray-600 rounded-xl cursor-pointer bg-orange-50/50 hover:bg-orange-50 hover:border-orange-400 transition-all duration-300 px-2">
                                            <div class="flex items-center gap-3">
                                                <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                </svg>
                                                <div class="flex flex-col text-left overflow-hidden">
                                                    <span class="text-xs font-bold text-gray-600 uppercase">Tải lên Bản vẽ
                                                        chi tiết</span>
                                                    <span id="task-pdf-name"
                                                        class="text-[10px] text-orange-600 font-medium truncate w-full">Định
                                                        dạng bất kỳ...</span>
                                                </div>
                                            </div>
                                            <input type="file" name="task_files[]" multiple class="hidden"
                                                onchange="displayFileName(this, 'task-pdf-name', 'orange')" />
                                        </label>
                                    </div>
                                @endif

                                <button type="submit" onclick="return confirm('Xác nhận lưu tài liệu?')"
                                    class="w-full flex justify-center items-center gap-2 px-8 py-3 text-sm font-black uppercase tracking-widest text-white bg-gray-800 hover:bg-gray-900 rounded-xl shadow-lg transition-all active:scale-95 h-12">
                                    Lưu & Cập nhật tài liệu
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

            </div>

            {{-- CÁC NÚT THAO TÁC (CHỈ DÀNH CHO THỢ) --}}
            @if ($user->role == 2)
                @if ($item->status == 'waiting')
                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 flex gap-4">
                        <form action="{{ route('nhanvien.job.reject', ['type' => $type, 'id' => $item->id]) }}"
                            method="POST" class="flex-1">
                            @csrf
                            <button type="submit" onclick="return confirm('Bạn có chắc chắn TỪ CHỐI?')"
                                class="w-full justify-center px-4 py-4 text-red-600 bg-red-50 hover:bg-red-100 font-bold rounded-xl transition-colors">
                                Từ chối nhận
                            </button>
                        </form>

                        <form action="{{ route('nhanvien.job.accept', ['type' => $type, 'id' => $item->id]) }}"
                            method="POST" class="flex-[2]">
                            @csrf
                            <button type="submit"
                                class="w-full justify-center px-4 py-4 text-white bg-blue-600 hover:bg-blue-700 shadow-lg font-bold rounded-xl transition-all active:scale-95 uppercase">
                                Xác nhận Làm ngay
                            </button>
                        </form>
                    </div>
                @elseif ($item->status == 'processing' && $type == 'task')
                    @php
                        $remaining = $item->item_count - $item->completed_count;
                    @endphp

                    @if ($remaining > 0)
                        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                            <div
                                class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 p-4 rounded-t-2xl flex items-start gap-3">
                                <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-bold text-yellow-800 dark:text-yellow-400">Lưu ý:</h4>
                                    <p class="text-xs text-yellow-700 dark:text-yellow-500 mt-1">
                                        Hệ thống hoạt động theo cơ chế <b>cộng dồn</b>. Bạn chỉ cần nhập số lượng sản phẩm
                                        <b>mới làm xong</b>, hệ thống sẽ tự động cộng với số lượng đã làm xong trước đây.
                                        Khi số lượng đã xong bằng với số lượng yêu cầu công việc này sẽ tự động chuyển sang
                                        trạng thái chờ nghiệm thu.
                                    </p>
                                </div>
                            </div>

                            <form
                                action="{{ route('nhanvien.job.update_progress', ['type' => $type, 'id' => $item->id]) }}"
                                method="POST"
                                class="bg-white dark:bg-gray-800 p-6 rounded-b-2xl border-x border-b border-yellow-200 dark:border-yellow-700 shadow-sm relative overflow-hidden">
                                @csrf
                                <div class="absolute -right-4 -bottom-4 opacity-5 pointer-events-none">
                                    <i class="voyager-pie-chart" style="font-size: 120px;"></i>
                                </div>

                                <div class="relative z-10 w-full">
                                    <label
                                        class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-3 block uppercase tracking-wide">
                                        Nhập số lượng vừa làm xong:
                                    </label>

                                    <div class="flex flex-col md:flex-row items-stretch gap-4">
                                        <div
                                            class="flex-1 flex items-center gap-4 bg-gray-50 dark:bg-gray-900 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
                                            <span class="text-sm font-bold text-gray-400 pl-2">+</span>
                                            <input type="number" name="add_count" min="1"
                                                max="{{ $remaining }}" required
                                                class="form-control rounded-lg border-blue-300 focus:border-blue-500 focus:ring-blue-500 text-2xl font-black text-blue-600 text-center w-28 py-2 shadow-inner m-0"
                                                placeholder="0">
                                            <div class="flex flex-col text-sm">
                                                <span class="text-xs text-red-500 font-medium">(Chỉ còn:
                                                    {{ $remaining }})</span>
                                            </div>
                                        </div>

                                        <button type="submit"
                                            onclick="return confirm('Xác nhận cộng thêm số lượng này?')"
                                            class="w-full md:w-auto px-8 py-4 md:py-0 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all active:scale-95 flex items-center justify-center gap-2 uppercase tracking-wider text-sm">
                                            <i class="voyager-plus"></i> Cập nhật tiến độ
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                @elseif ($item->status == 'pending_review')
                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                        <div
                            class="bg-green-50 dark:bg-green-900/20 p-5 rounded-2xl border border-green-200 dark:border-green-800/30 flex items-center gap-4 text-green-700 dark:text-green-400">
                            <div class="p-3 bg-green-100 dark:bg-green-900/50 rounded-full">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-lg font-black uppercase tracking-wide">Tuyệt vời! Đã xong 100%</p>
                                <p class="text-sm font-medium mt-1">Bạn đã hoàn thành đủ số lượng yêu cầu. Đang chờ quản lý
                                    nghiệm thu.</p>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
            {{-- KẾT THÚC CÁC NÚT THAO TÁC CỦA THỢ --}}

            @if ($item->status == 'completed')
                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <div
                        class="bg-green-50 dark:bg-green-900/20 p-5 rounded-2xl border border-green-200 dark:border-green-800/30 flex items-center gap-4 text-green-700 dark:text-green-400">
                        <div class="p-3 bg-green-100 dark:bg-green-900/50 rounded-full">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg font-black uppercase tracking-wide">Tuyệt vời! Đã xong 100%</p>
                            <p class="text-sm font-medium mt-1">Nhiệm vụ này đã được hoàn thành.</p>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function displayFileName(input, targetId, colorClass) {
            const display = document.getElementById(targetId);
            if (input.files && input.files.length > 0) {
                if (input.files.length > 1) {
                    display.textContent = `✓ Đã chọn ${input.files.length} tệp`;
                } else {
                    display.textContent = "✓ " + input.files[0].name;
                }
                display.className = `text-[10px] text-green-600 font-bold truncate w-full`;
            } else {
                display.textContent = "Định dạng bất kỳ...";
                display.className = `text-[10px] text-${colorClass}-600 font-medium truncate w-full`;
            }
        }
    </script>
@endpush
