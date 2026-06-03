@extends('layouts.app')

@section('title', 'Lịch sử phiếu lương')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">

        <div
            class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 border-b border-gray-100 dark:border-gray-800 pb-4 gap-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                Lịch sử phiếu lương & ứng tiền
            </h2>
            <a href="{{ route('nhanvien.salary_advance.create') }}"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Tạo yêu cầu mới
            </a>
        </div>

        @if ($payrolls->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($payrolls as $payroll)
                    <a href="{{ route('nhanvien.salary_advance.show', $payroll->id) }}"
                        class="block bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 transition-all group">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3
                                    class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-blue-600 transition-colors">
                                    Tháng {{ $payroll->month }}/{{ $payroll->year }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ngày tạo:
                                    {{ $payroll->created_at->format('d/m/Y') }}</p>
                            </div>

                            @if ($payroll->status == 'paid')
                                <span
                                    class="px-3 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 text-xs font-bold rounded-full border border-green-200 dark:border-green-800">
                                    Đã duyệt
                                </span>
                            @else
                                <span
                                    class="px-3 py-1 bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 text-xs font-bold rounded-full border border-orange-200 dark:border-orange-800">
                                    Đang chờ
                                </span>
                            @endif
                        </div>

                        <div class="space-y-2 border-t border-gray-100 dark:border-gray-700 pt-4 mt-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Nghiệm thu trong tháng:</span>
                                <span
                                    class="font-semibold text-gray-900 dark:text-white">{{ number_format($payroll->earned_amount) }}
                                    đ</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Đã xin ứng:</span>
                                <span
                                    class="font-bold text-blue-600 dark:text-blue-400">{{ number_format($payroll->withdrawn_amount) }}
                                    đ</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $payrolls->links() }}
            </div>
        @else
            <div
                class="flex flex-col items-center justify-center py-20 px-4 text-center bg-white dark:bg-gray-800 rounded-3xl border border-dashed border-gray-200 dark:border-gray-700">
                <div
                    class="w-20 h-20 mb-4 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center text-gray-400">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <p class="text-gray-500 font-medium mb-4">Bạn chưa có phiếu lương / lịch sử ứng tiền nào.</p>
            </div>
        @endif
    </div>
@endsection
