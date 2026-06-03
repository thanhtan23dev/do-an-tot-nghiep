@extends('layouts.app')

@section('title', 'Yêu cầu ứng lương')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">

        <div class="flex items-center justify-between mb-6 border-b border-gray-100 dark:border-gray-800 pb-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
                <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg text-green-600 dark:text-green-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                Tạo yêu cầu ứng lương
            </h2>
        </div>



        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Số dư két sắt</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($user->total_balance) }} <span
                        class="text-sm font-normal">VNĐ</span></p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Đang chờ duyệt</p>
                <p class="text-xl font-bold text-orange-500">{{ number_format($pendingAmount) }} <span
                        class="text-sm font-normal text-gray-900 dark:text-white">VNĐ</span></p>
            </div>
            <div
                class="bg-blue-50 dark:bg-blue-900/20 p-5 rounded-2xl shadow-sm border border-blue-100 dark:border-blue-800/30">
                <p class="text-sm font-bold text-blue-600 dark:text-blue-400 mb-1">CÒN CÓ THỂ ỨNG</p>
                <p class="text-2xl font-black text-blue-700 dark:text-blue-300">{{ number_format($availableBalance) }} <span
                        class="text-base font-medium">VNĐ</span></p>
            </div>
        </div>


        <div
            class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-10 shadow-sm border border-gray-100 dark:border-gray-700">
            <form action="{{ route('nhanvien.salary_advance.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Số tiền muốn ứng (VNĐ) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="withdrawn_amount" value="{{ old('withdrawn_amount') }}" required
                            min="10000" max="{{ $availableBalance }}" placeholder="Nhập số tiền (VD: 500000)"
                            class="w-full pl-4 pr-12 py-3 md:w-1/2 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        <span
                            class="absolute right-4 md:right-[calc(50%+1rem)] top-1/2 -translate-y-1/2 text-gray-500 font-medium">VNĐ</span>
                    </div>
                    @error('withdrawn_amount')
                        <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-2 italic">Lưu ý: Bạn chỉ có thể ứng tối đa bằng với số dư khả dụng
                        hiện tại.</p>
                </div>

                <div class="pt-4 flex justify-start">
                    <button type="submit" {{ $availableBalance <= 0 ? 'disabled' : '' }}
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-bold py-3 px-8 rounded-xl transition-colors shadow-lg shadow-blue-500/30 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Gửi yêu cầu ứng lương
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
