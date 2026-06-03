@extends('layouts.app')
@section('title', 'Tổng quan xưởng')

@section('content')
    <div class="max-w-5xl mx-auto space-y-8">

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-800 dark:text-white tracking-tight">
                    Chào {{ auth('nhan_vien')->user()->name }}! 👋
                </h2>
                <p class="text-gray-500 font-medium">Hôm nay bạn có muốn hoàn thành thêm bao nhiêu việc?</p>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">

            <div
                class="bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
                <div
                    class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 text-blue-600 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Công việc mới</div>
                <div class="text-2xl font-black text-gray-900 dark:text-white">{{ $stats['new_jobs'] }}</div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
                <div
                    class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 text-purple-600 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Công việc đang làm</div>
                <div class="text-2xl font-black text-gray-900 dark:text-white">{{ $stats['active_jobs'] }}</div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 p-5 rounded-3xl shadow-sm border border-green-100 dark:border-green-800/30 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 opacity-5 pointer-events-none">
                    <svg class="w-32 h-32 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                    </svg>
                </div>
                <div
                    class="w-10 h-10 bg-green-100 dark:bg-green-900/30 text-green-600 rounded-xl flex items-center justify-center mb-4 relative z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div
                    class="text-green-600 dark:text-green-500 text-xs font-black uppercase tracking-wider mb-1 relative z-10">
                    Tổng số dư hiện tại</div>
                <div class="text-xl font-black text-green-700 dark:text-green-400 relative z-10">
                    {{ number_format($stats['total_balance'] ?? 0, 0, ',', '.') }} đ
                </div>
            </div>

        </div>

        <div
            class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                    Công việc gấp (Sắp hết hạn)
                </h3>
            </div>

            @if ($nearDeadlines->count() > 0)
                <div class="divide-y divide-gray-50 dark:divide-gray-700">
                    @foreach ($nearDeadlines as $job)
                        <div
                            class="p-6 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-900/20 transition-all">
                            <div class="space-y-1">
                                <p class="font-bold text-gray-900 dark:text-white">{{ $job->name }}</p>
                                <p class="text-xs text-red-500 font-medium">Ngày hết hạn:
                                    {{ $job->deadline->format('H:i d/m') }}
                                </p>
                            </div>
                            <a href="{{ route('nhanvien.jobs.active') }}"
                                class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg group hover:bg-blue-600 transition-all">
                                <svg class="w-5 h-5 text-gray-500 group-hover:text-white" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-10 text-center">
                    <p class="text-gray-400 text-sm italic">Tuyệt vời! Không có công việc nào đang bị trễ hạn.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
