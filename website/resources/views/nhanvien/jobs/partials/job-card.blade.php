@php
    $isTask = $viewType == 'task';
    $status = $item->status;
    $role = auth('nhan_vien')->user()->role;
@endphp

<div
    class="bg-white dark:bg-gray-800 rounded-3xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden flex flex-col h-full hover:shadow-xl transition-all duration-300">


    @if ($status == 'rejected')
        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-red-500 shadow-[2px_0_10px_rgba(239,68,68,0.4)]"></div>
    @elseif($status == 'pending_review')
        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-orange-500 shadow-[2px_0_10px_rgba(249,115,22,0.4)]"></div>
    @elseif($status == 'completed')
        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-green-500 shadow-[2px_0_10px_rgba(34,197,94,0.4)]"></div>
    @else
        <div
            class="absolute left-0 top-0 bottom-0 w-1.5 {{ $viewType == 'order' ? 'bg-indigo-500 shadow-[2px_0_10px_rgba(99,102,241,0.4)]' : 'bg-blue-500 shadow-[2px_0_10px_rgba(59,130,246,0.4)]' }}">
        </div>
    @endif

    <div class="flex-grow pl-2">
        <a href="{{ route('nhanvien.job.show', ['type' => $viewType, 'id' => $item->id]) }}" class="group block">

            <div class="flex justify-between items-start mb-3">
                <div class="pr-2">
                    <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mb-1">
                        {{ $viewType == 'order' ? 'Mã Đơn: #' . $item->id : 'Đơn: ' . ($item->order->name ?? 'N/A') }}
                    </p>
                    <h4
                        class="text-base font-bold text-gray-900 dark:text-white group-hover:text-blue-600 transition-colors line-clamp-2">
                        {{ $item->name }}
                    </h4>
                </div>


                @if ($status == 'processing')
                    <span
                        class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/20 whitespace-nowrap">ĐANG
                        LÀM</span>
                @elseif($status == 'pending_review')
                    <span
                        class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-orange-50 text-orange-600 dark:bg-orange-900/20 whitespace-nowrap">CHỜ
                        DUYỆT</span>
                @elseif($status == 'rejected')
                    <span
                        class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-red-50 text-red-600 dark:bg-red-900/20 animate-pulse whitespace-nowrap">SỬA
                        LẠI</span>
                @elseif($status == 'completed')
                    <span
                        class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-green-50 text-green-600 dark:bg-green-900/20 whitespace-nowrap">ĐÃ
                        XONG</span>
                @elseif($status == 'waiting')
                    <span
                        class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 whitespace-nowrap">VIỆC
                        MỚI</span>
                @endif
            </div>


            @if ($status == 'rejected' && $isTask && $item->reject_reason)
                <div
                    class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-2xl border border-red-100 dark:border-red-800/30">
                    <p class="text-xs text-red-600 dark:text-red-400 leading-relaxed font-medium line-clamp-2">
                        <span class="font-bold"><i class="voyager-warning"></i> Sếp chú thích:</span>
                        {{ $item->reject_reason }}
                    </p>
                </div>
            @endif


            @if ($role == 2 && $isTask && in_array($status, ['processing', 'rejected']))
                @php
                    $total = $item->item_count ?? 1;
                    $completed = $item->completed_count ?? 0;
                    $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
                @endphp
                <div
                    class="mb-4 bg-gray-50 dark:bg-gray-800/50 p-3 rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="flex justify-between items-end mb-1.5">
                        <span class="text-[11px] font-bold text-gray-500 uppercase">Tiến độ hiện tại</span>
                        <span
                            class="text-xs font-black {{ $percent == 100 ? 'text-green-600' : 'text-blue-600' }}">{{ $completed }}/{{ $total }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all duration-500 {{ $percent == 100 ? 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.5)]' : 'bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.5)]' }}"
                            style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            @endif

        </a>
    </div>

    <div class="pt-4 mt-auto border-t border-gray-100 dark:border-gray-700/50 pl-2">

        {{ $actions ?? '' }}



        @if ($role == 1)
            @if ($status != 'completed')
                <form action="{{ route('nhanvien.job.submit', ['type' => $viewType, 'id' => $item->id]) }}"
                    method="POST" enctype="multipart/form-data" class="mt-2">
                    @csrf

                    @if ($viewType == 'order')
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <label
                                class="flex flex-col items-center justify-center p-3 border border-dashed border-blue-200 dark:border-gray-600 rounded-xl cursor-pointer bg-blue-50/30 hover:bg-blue-50 transition-colors">
                                <svg class="w-5 h-5 text-blue-500 mb-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                <span class="text-[9px] font-bold text-gray-600 uppercase text-center">Bản vẽ
                                    (Ảnh)</span>
                                <span id="img-name-{{ $item->id }}"
                                    class="text-[9px] text-blue-600 font-medium mt-0.5 truncate w-full text-center">Tất
                                    cả...</span>
                                <input type="file" name="design_image[]" multiple class="hidden"
                                    onchange="displayFileName(this, 'img-name-{{ $item->id }}', 'blue')" />
                            </label>

                            <label
                                class="flex flex-col items-center justify-center p-3 border border-dashed border-red-200 dark:border-gray-600 rounded-xl cursor-pointer bg-red-50/30 hover:bg-red-50 transition-colors">
                                <svg class="w-5 h-5 text-red-500 mb-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="text-[9px] font-bold text-gray-600 uppercase text-center">Quy cách</span>
                                <span id="pdf-name-{{ $item->id }}"
                                    class="text-[9px] text-red-600 font-medium mt-0.5 truncate w-full text-center">Tất
                                    cả...</span>
                                <input type="file" name="quy_cach[]" multiple class="hidden"
                                    onchange="displayFileName(this, 'pdf-name-{{ $item->id }}', 'red')" />
                            </label>
                        </div>
                    @else
                        <div class="mb-3">
                            <label
                                class="flex flex-col items-center justify-center p-3 border border-dashed border-orange-200 dark:border-gray-600 rounded-xl cursor-pointer bg-orange-50/30 hover:bg-orange-50 transition-colors">
                                <svg class="w-5 h-5 text-orange-500 mb-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                <span class="text-[9px] font-bold text-gray-600 uppercase text-center">Tải lên Bản vẽ
                                    chi tiết</span>
                                <span id="task-file-name-{{ $item->id }}"
                                    class="text-[9px] text-orange-600 font-medium mt-0.5 truncate w-full text-center">Định
                                    dạng bất kỳ...</span>
                                <input type="file" name="task_files[]" multiple class="hidden"
                                    onchange="displayFileName(this, 'task-file-name-{{ $item->id }}', 'orange')" />
                            </label>
                        </div>
                    @endif

                    <button type="submit" onclick="return confirm('Xác nhận nộp tài liệu và gửi sếp duyệt?')"
                        class="w-full flex justify-center items-center gap-2 px-4 py-2.5 text-[11px] font-black uppercase tracking-widest text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition-all active:scale-95">
                        Gửi duyệt tài liệu
                    </button>
                </form>
            @endif
        @else
            @if (in_array($status, ['processing', 'rejected']))
                <a href="{{ route('nhanvien.job.show', ['type' => $viewType, 'id' => $item->id]) }}"
                    class="w-full flex justify-center items-center gap-2 px-4 py-3 text-xs font-black uppercase tracking-widest text-blue-600 bg-blue-50 border border-blue-200 hover:bg-blue-600 hover:text-white rounded-xl transition-all active:scale-95 group/btn">
                    Cập nhật tiến độ
                    <svg class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
            @elseif($status == 'pending_review')
                <div
                    class="w-full flex flex-col items-center justify-center py-3 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-700">
                    <svg class="w-5 h-5 text-orange-400 animate-spin mb-1" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span class="text-[10px] font-black text-orange-600 uppercase tracking-widest">Đang chờ xét
                        duyệt</span>
                </div>
            @endif
        @endif
    </div>

    @if (isset($isHistory) && $isHistory == true)
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700/50">
            <div class="flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">
                        {{ isset($item->monthly_payroll_id) ? 'Đã duyệt vào phiếu lương' : 'Ngày xong' }}
                    </span>
                    <span class="text-xs text-gray-600 dark:text-gray-300 font-medium">
                        {{ isset($item->monthly_payroll_id) ? 'Tháng ' . $item->monthlyPayroll->month . '/' . $item->monthlyPayroll->year : $item->updated_at->format('d/m/Y H:i') }}
                    </span>
                </div>

                @if ($viewType == 'task' && $role != 1)
                    <div class="flex flex-col items-end">
                        <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Thù lao</span>
                        <span
                            class="text-lg font-black {{ isset($item->monthly_payroll_id) ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">
                            +{{ number_format($item->reward, 0, ',', '.') }} đ
                        </span>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
