@php
    $brandName = setting('site.nhan_hieu', 'ĐMN TÂM AN');
    $mini_des = setting('site.mini_des');
@endphp
<aside :class="sidebarToggle ? 'translate-x-0' : '-translate-x-full'"
    class="sidebar fixed left-0 top-0 z-[9999] flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 dark:border-gray-800 dark:bg-gray-900 transition-transform duration-300 ease-linear lg:static lg:translate-x-0"
    @click.outside="sidebarToggle = false">

    <div class="flex items-center justify-between gap-2 pt-8 pb-7 sidebar-header">
        <a href="{{ route('nhanvien.dashboard') }}" class="flex items-center gap-3">
            <div class="flex items-center gap-3 group cursor-pointer select-none">

                <div
                    class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 group-hover:scale-105 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                    </svg>
                </div>

                <div class="flex flex-col justify-center">
                    <span
                        class="font-black text-xl text-gray-800 dark:text-white tracking-wide uppercase group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors leading-tight">
                        {{ $brandName }}
                    </span>
                    <span
                        class="text-[10px] text-blue-600 dark:text-blue-400 font-bold uppercase tracking-[0.15em] leading-none">
                        {{ $mini_des }}
                    </span>
                </div>
            </div>
        </a>

        <button class="lg:hidden text-gray-500" @click.stop="sidebarToggle = false">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
        <nav class="mt-5 px-2 lg:mt-9" x-data="{ selected: '{{ Request::route()->getName() }}' }">

            <div class="mb-4 text-xs uppercase leading-[20px] text-gray-400 font-semibold tracking-wider px-3">
                QUẢN LÝ CÔNG VIỆC
            </div>

            <ul class="flex flex-col gap-2">
                <li>
                    <a href="{{ route('nhanvien.dashboard') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-200
                            {{ Request::routeIs('nhanvien.dashboard')
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-500/30 dark:shadow-none'
                                : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span class="truncate">Tổng quan</span>
                    </a>
                </li>

                @if (auth('nhan_vien')->user()->role == 2)
                    <li>
                        <a href="{{ route('nhanvien.jobs.new') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-200
                            {{ Request::routeIs('nhanvien.jobs.new')
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-500/30 dark:shadow-none'
                                : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span class="truncate">Công việc mới</span>
                        </a>
                    </li>
                @endif

                <li>
                    <a href="{{ route('nhanvien.jobs.active') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-200
                            {{ Request::routeIs('nhanvien.jobs.active')
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-500/30 dark:shadow-none'
                                : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        <span class="truncate">Công việc đang làm</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('nhanvien.jobs.completed') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-200
                            {{ Request::routeIs('nhanvien.jobs.completed')
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-500/30 dark:shadow-none'
                                : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="truncate">Đã hoàn thành</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('nhanvien.salary_advance.index') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-200
                            {{ Request::routeIs('nhanvien.salary_advance.*')
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-500/30 dark:shadow-none'
                                : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="truncate">Phiếu ứng lương</span>
                    </a>
                </li>
            </ul>

            <div class="mt-8 mb-4 text-xs uppercase leading-[20px] text-gray-400 font-semibold tracking-wider px-3">
                TÀI KHOẢN
            </div>

            <ul class="flex flex-col gap-2">
                <li>
                    <a href="{{ route('nhanvien.profile.edit') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-200 
                        {{ Request::routeIs('nhanvien.profile.edit')
                            ? 'bg-blue-600 text-white shadow-md shadow-blue-500/30 dark:shadow-none'
                            : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="truncate">Hồ sơ cá nhân</span>
                    </a>
                </li>

                <li>
                    <form action="{{ route('nhanvien.logout') }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-200 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">

                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span class="truncate">Đăng xuất</span>
                        </button>
                    </form>
                </li>
            </ul>
        </nav>
    </div>
</aside>
