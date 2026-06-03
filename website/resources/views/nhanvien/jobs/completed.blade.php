@extends('layouts.app')

@section('title', 'Lịch sử công việc')

@section('content')
    <div class="max-w-5xl mx-auto space-y-10">


        <div
            class="flex flex-col md:flex-row items-start md:items-center justify-between mb-2 border-b border-gray-100 dark:border-gray-800 pb-4 gap-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
                <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg text-green-600 dark:text-green-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                Lịch sử công việc
            </h2>

            <form action="{{ route('nhanvien.jobs.completed') }}" method="GET" class="w-full md:w-auto relative">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Tìm tên công việc hoặc mã..."
                    class="w-full md:w-72 pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500 transition-all dark:text-white" />

                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>

                @if (request('search'))
                    <a href="{{ route('nhanvien.jobs.completed') }}"
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


            <div class="space-y-4 mb-10">
                <h3
                    class="text-lg font-black text-gray-800 dark:text-white flex items-center gap-2 uppercase tracking-wide">
                    <span class="w-3 h-3 rounded-full bg-purple-500"></span>
                    Thiết kế đơn hàng đã hoàn thành
                </h3>

                @if (isset($completedOrders) && $completedOrders->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($completedOrders as $item)
                            @include('nhanvien.jobs.partials.job-card', [
                                'item' => $item,
                                'viewType' => 'order',
                                'isHistory' => true,
                            ])
                        @endforeach
                    </div>
                    <div class="mt-6">
                        {{ $completedOrders->appends(request()->except('orders_page'))->links() }}
                    </div>
                @else
                    <div
                        class="p-8 text-center bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                        <p class="text-gray-500 font-medium text-sm">
                            {{ request('search') ? 'Không tìm thấy thiết kế đơn hàng nào khớp với từ khóa.' : 'Chưa có thiết kế đơn hàng nào hoàn thành.' }}
                        </p>
                    </div>
                @endif
            </div>


            <div class="space-y-4">
                <h3
                    class="text-lg font-black text-gray-800 dark:text-white flex items-center gap-2 uppercase tracking-wide">
                    <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                    Thiết kế công việc đã hoàn thành
                </h3>

                @if (isset($completedTasks) && $completedTasks->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($completedTasks as $item)
                            @include('nhanvien.jobs.partials.job-card', [
                                'item' => $item,
                                'viewType' => 'task',
                                'isHistory' => true,
                            ])
                        @endforeach
                    </div>
                    <div class="mt-6">
                        {{ $completedTasks->appends(request()->except('tasks_page'))->links() }}
                    </div>
                @else
                    <div
                        class="p-8 text-center bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                        <p class="text-gray-500 font-medium text-sm">
                            {{ request('search') ? 'Không tìm thấy thiết kế công việc nào khớp với từ khóa.' : 'Chưa có thiết kế công việc nào hoàn thành.' }}
                        </p>
                    </div>
                @endif
            </div>
        @else
            <div class="space-y-4">
                <h3
                    class="text-lg font-black text-gray-800 dark:text-white flex items-center gap-2 uppercase tracking-wide">
                    <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                    Công việc đã hoàn thành
                </h3>

                @if (isset($completedItems) && $completedItems->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($completedItems as $item)
                            @include('nhanvien.jobs.partials.job-card', [
                                'item' => $item,
                                'viewType' => $viewType,
                                'isHistory' => true,
                            ])
                        @endforeach
                    </div>
                    <div class="mt-6">
                        {{ $completedItems->links() }}
                    </div>
                @else
                    <div
                        class="p-8 text-center bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                        <p class="text-gray-500 font-medium text-sm">
                            {{ request('search') ? 'Không tìm thấy công việc nào khớp với từ khóa.' : 'Chưa có công việc nào hoàn thành.' }}
                        </p>
                    </div>
                @endif
            </div>
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
                display.textContent = targetId.includes('pdf') ? "Đổi Tệp PDF..." : "Đổi JPG, PNG...";
                display.className = `text-[9px] text-${colorClass}-600 font-medium truncate w-full text-center mt-0.5`;
            }
        }
    </script>
@endpush
