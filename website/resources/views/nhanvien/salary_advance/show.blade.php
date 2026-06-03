@extends('layouts.app')

@section('title', 'Chi tiết phiếu lương')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Nút quay lại --}}
        <div>
            <a href="{{ route('nhanvien.salary_advance.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 transition-colors gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Quay lại danh sách
            </a>
        </div>

        {{-- Khung Biên lai --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-10 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden">

            {{-- Badge Trạng thái --}}
            <div class="absolute top-6 right-6">
                @if ($payroll->status == 'paid')
                    <span
                        class="px-4 py-1.5 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 text-sm font-bold rounded-full border border-green-200 dark:border-green-800">
                        ✓ Đã thanh toán / Duyệt
                    </span>
                @else
                    <span
                        class="px-4 py-1.5 bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 text-sm font-bold rounded-full border border-orange-200 dark:border-orange-800">
                        ⧗ Đang chờ xử lý
                    </span>
                @endif
            </div>

            <h2 class="text-3xl font-black text-gray-900 dark:text-white mb-2">Phiếu Lương & Ứng Tiền</h2>
            <p class="text-gray-500 dark:text-gray-400 font-medium mb-8">Kỳ lương: Tháng {{ $payroll->month }} năm
                {{ $payroll->year }}</p>

            {{-- Thông số tài chính --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <div class="p-5 rounded-2xl bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-600">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Tổng nghiệm thu trong kỳ</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">+
                        {{ number_format($payroll->earned_amount) }} <span class="text-sm font-normal">VNĐ</span></p>
                </div>

                <div class="p-5 rounded-2xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/30">
                    <p class="text-sm font-medium text-blue-600 dark:text-blue-400 mb-1">Số tiền bạn yêu cầu ứng</p>
                    <p class="text-2xl font-black text-blue-700 dark:text-blue-300">-
                        {{ number_format($payroll->withdrawn_amount) }} <span class="text-sm font-normal">VNĐ</span></p>
                </div>
            </div>

            {{-- Phần Quản lý phản hồi (Nếu có trừ tiền) --}}
            @if ($payroll->deduction_amount > 0 || $payroll->deduction_note)
                <div class="mb-8 p-5 rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/30">
                    <h3 class="text-sm font-bold text-red-600 dark:text-red-400 mb-3 uppercase tracking-wider">Phản hồi từ
                        Quản lý</h3>
                    <div class="space-y-3">
                        @if ($payroll->deduction_amount > 0)
                            <div
                                class="flex justify-between items-center pb-3 border-b border-red-200/50 dark:border-red-800/50">
                                <span class="text-red-800 dark:text-red-300 font-medium">Khoản khấu trừ thêm:</span>
                                <span class="font-bold text-red-600 dark:text-red-400">-
                                    {{ number_format($payroll->deduction_amount) }} VNĐ</span>
                            </div>
                        @endif

                        @if ($payroll->deduction_note)
                            <div>
                                <span class="block text-red-800 dark:text-red-300 font-medium mb-1">Ghi chú:</span>
                                <p
                                    class="text-sm text-red-700 dark:text-red-400 italic bg-white/50 dark:bg-black/20 p-3 rounded-lg">
                                    {{ $payroll->deduction_note }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <hr class="border-gray-100 dark:border-gray-700 my-8">

            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Các công việc đã nghiệm thu trong phiếu này
                </h3>

                @if ($payroll->tasks->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 border-y border-gray-200 dark:border-gray-700">
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Tên công
                                        việc</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Hoàn thành
                                    </th>
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 text-right">
                                        Tiền công</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($payroll->tasks as $task)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">
                                            {{ $task->name ?? 'Công việc #' . $task->id }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $task->updated_at->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-bold text-right">
                                            {{ number_format($task->reward) }} đ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500 italic bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl">Không có công việc
                        cụ thể nào được đính kèm, phiếu này chỉ lưu yêu cầu ứng tiền.</p>
                @endif
            </div>

        </div>
    </div>
@endsection
