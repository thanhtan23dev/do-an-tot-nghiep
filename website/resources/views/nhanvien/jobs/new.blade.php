@extends('layouts.app')

@section('title', 'Công việc mới giao')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">

        <div
            class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 border-b border-gray-100 dark:border-gray-800 pb-4 gap-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                Việc mới chờ thợ nhận
            </h2>

            <form action="{{ route('nhanvien.jobs.new') }}" method="GET" class="w-full md:w-auto relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm tên việc hoặc mã..."
                    class="w-full md:w-72 pl-10 pr-10 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all dark:text-white" />
                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </form>
        </div>

        @if ($activeItems->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($activeItems as $item)
                    @component('nhanvien.jobs.partials.job-card', ['item' => $item, 'viewType' => 'task'])
                        @slot('actions')
                            <div class="flex items-center gap-3 pt-2">
                                <form action="{{ route('nhanvien.job.reject', ['type' => 'task', 'id' => $item->id]) }}"
                                    method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit"
                                        onclick="return confirm('Từ chối công việc này? Quản lý sẽ nhận được thông báo.')"
                                        class="w-full flex justify-center items-center gap-2 px-3 py-2.5 text-sm font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40">
                                        Từ chối
                                    </button>
                                </form>

                                <form action="{{ route('nhanvien.job.accept', ['type' => 'task', 'id' => $item->id]) }}"
                                    method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex justify-center items-center gap-2 px-3 py-2.5 text-sm font-semibold text-white bg-green-500 hover:bg-green-600 shadow-sm shadow-green-500/30 rounded-xl transition-colors">
                                        Nhận việc
                                    </button>
                                </form>
                            </div>
                        @endslot
                    @endcomponent
                @endforeach
            </div>

            <div class="mt-6">
                {{ $activeItems->links() }}
            </div>
        @else
            <div
                class="flex flex-col items-center justify-center py-20 text-center bg-white dark:bg-gray-800 rounded-3xl border border-dashed border-gray-200 dark:border-gray-700">
                <div
                    class="w-20 h-20 mb-4 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center text-gray-400">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" />
                    </svg>
                </div>
                <p class="text-gray-500 font-medium">Hiện không có công việc mới nào chờ bạn nhận.</p>
            </div>
        @endif
    </div>
@endsection
