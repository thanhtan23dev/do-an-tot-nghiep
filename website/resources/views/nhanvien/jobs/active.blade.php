@extends('layouts.app')

@section('title', 'Công việc đang làm')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">


        <div
            class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 border-b border-gray-100 dark:border-gray-800 pb-4 gap-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                </div>
                Công việc đang thực hiện
            </h2>

            <form action="{{ route('nhanvien.jobs.active') }}" method="GET" class="w-full md:w-auto relative">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Tìm tên công việc hoặc mã..."
                    class="w-full md:w-72 pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all dark:text-white" />

                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>

                @if (request('search'))
                    <a href="{{ route('nhanvien.jobs.active') }}"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </form>
        </div>


        @if (auth('nhan_vien')->user()->role == 1)


            <div class="mb-10">
                <h3
                    class="text-xl font-black text-gray-800 dark:text-gray-200 mb-4 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-6 bg-purple-500 rounded-full"></span>
                    Thiết kế Đơn Hàng
                </h3>

                @if (isset($activeOrders) && $activeOrders->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($activeOrders as $item)
                            @include('nhanvien.jobs.partials.job-card', [
                                'item' => $item,
                                'viewType' => 'order',
                            ])
                        @endforeach
                    </div>
                    <div class="mt-6">
                        {{ $activeOrders->appends(request()->except('orders_page'))->links() }}
                    </div>
                @else
                    <div
                        class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-6 text-center border border-dashed border-gray-200 dark:border-gray-700">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">Bạn không có yêu cầu Thiết kế Đơn hàng nào
                            đang chờ.</p>
                    </div>
                @endif
            </div>


            <div>
                <h3
                    class="text-xl font-black text-gray-800 dark:text-gray-200 mb-4 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-6 bg-orange-500 rounded-full"></span>
                    Thiết kế Công Việc
                </h3>

                @if (isset($activeTasks) && $activeTasks->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($activeTasks as $item)
                            @include('nhanvien.jobs.partials.job-card', [
                                'item' => $item,
                                'viewType' => 'task',
                            ])
                        @endforeach
                    </div>
                    <div class="mt-6">
                        {{ $activeTasks->appends(request()->except('tasks_page'))->links() }}
                    </div>
                @else
                    <div
                        class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-6 text-center border border-dashed border-gray-200 dark:border-gray-700">
                        <p class="text-gray-500 dark:text-gray-400 font-medium">Bạn không có yêu cầu Thiết kế Công việc chi
                            tiết nào đang chờ.</p>
                    </div>
                @endif
            </div>
        @else
            @if (isset($activeItems) && $activeItems->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($activeItems as $item)
                        @include('nhanvien.jobs.partials.job-card', [
                            'item' => $item,
                            'viewType' => $viewType,
                        ])
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $activeItems->links() }}
                </div>
            @else
                <div
                    class="flex flex-col items-center justify-center py-20 px-4 text-center bg-white dark:bg-gray-800 rounded-3xl border border-dashed border-gray-200 dark:border-gray-700">
                    <div
                        class="w-20 h-20 mb-4 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center text-gray-400">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>

                    @if (request('search'))
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Không tìm thấy kết quả</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto text-center">
                            Không có công việc nào đang làm khớp với từ khóa "<span
                                class="font-bold text-gray-700 dark:text-gray-300">{{ request('search') }}</span>".
                        </p>
                        <a href="{{ route('nhanvien.jobs.active') }}"
                            class="mt-4 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-sm font-semibold hover:bg-blue-100 transition-colors">
                            Xóa tìm kiếm
                        </a>
                    @else
                        <p class="text-gray-500 font-medium">Bạn hiện không có công việc nào đang làm.</p>
                    @endif
                </div>
            @endif
        @endif

    </div>
@endsection

@push('scripts')
    <script>
        function displayFileName(input, targetId, colorClass) {
            const display = document.getElementById(targetId);
            if (input.files.length > 0) {
                display.textContent = "✓ " + input.files[0].name;
                display.className = `text-[9px] text-green-600 font-bold truncate w-full text-center mt-0.5`;
            } else {
                display.textContent = targetId.includes('pdf') ? "Tệp PDF..." : "JPG, PNG...";
                display.className = `text-[9px] text-${colorClass}-600 font-medium truncate w-full text-center mt-0.5`;
            }
        }
    </script>
@endpush
