@extends('voyager::master')

@section('page_title', 'Trang tổng quan')

@section('content')
    <div class="page-content" style="padding-top: 0 !important; margin-top: 0 !important;">

        @include('voyager::alerts')

        <div
            style="background-color: #fff; padding: 20px 30px; border-bottom: 1px solid #e0e0e0; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">

            <div style="display: flex; align-items: center; gap: 15px;">
                <div
                    style="width: 50px; height: 50px; background-color: #e0f2fe; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i class="voyager-bar-chart" style="font-size: 30px; color: #3490dc; margin: 0;"></i>
                </div>
                <div>
                    <h1 style="margin: 0; font-size: 24px; font-weight: bold; color: #333; line-height: 1.2;">Trang tổng quan
                    </h1>
                    <p style="margin: 3px 0 0 0; color: #777; font-size: 13px;">Hệ thống thống kê và quản lý tiến độ xưởng
                        ĐMN Tâm An</p>
                </div>
            </div>
        </div>

        <div class="container-fluid">

            <div class="row">
                <div class="col-md-4 col-sm-12">
                    <div class="panel widget center bgimage"
                        style="background-color: #3b82f6; color:white; border-radius: 8px; box-shadow: 0 2px 10px rgba(59, 130, 246, 0.2);">
                        <div class="dimmer"></div>
                        <div class="panel-content" style="padding: 20px;">
                            <i class="voyager-bag" style="font-size: 40px; margin-bottom: 10px; opacity: 0.8;"></i>
                            <h4 style="font-weight: bold; margin-bottom: 5px;">{{ $thisMonthOrders }} Đơn</h4>
                            <p style="text-transform: uppercase; font-size: 11px; letter-spacing: 1px; margin:0;">Tổng đơn
                                hàng tháng này</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="panel widget center bgimage"
                        style="background-color: #8b5cf6; color:white; border-radius: 8px; box-shadow: 0 2px 10px rgba(139, 92, 246, 0.2);">
                        <div class="dimmer"></div>
                        <div class="panel-content" style="padding: 20px;">
                            <i class="voyager-check-circle" style="font-size: 40px; margin-bottom: 10px; opacity: 0.8;"></i>
                            <h4 style="font-weight: bold; margin-bottom: 5px;">{{ $thisMonthCompletedOrders }} /
                                {{ $thisMonthOrders }}</h4>
                            <p style="text-transform: uppercase; font-size: 11px; letter-spacing: 1px; margin:0;">Đơn hàng
                                đã hoàn thành</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default" style="box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-radius: 8px;">
                <div class="panel-heading" style="background-color: #00bd26; border-bottom: 1px solid #eee;">
                    <h3 class="panel-title" style="font-weight: bold; color: #ffffff;">
                        <i class="voyager-activity"></i> THEO DÕI TIẾN ĐỘ ĐƠN HÀNG ĐANG THI CÔNG
                    </h3>
                </div>
                <div class="panel-body" style="padding: 0;">
                    @if (isset($activeOrders) && $activeOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" style="margin-bottom: 0;">
                                <thead style="background-color: #f9f9f9;">
                                    <tr>
                                        <th width="15%">Tên Đơn Hàng</th>
                                        <th width="30%">Thanh Tiến Độ Kép</th>
                                        <th width="10%" class="text-center">Chỉ số SPI</th>
                                        <th width="15%" class="text-center">Trạng thái (Hệ thống)</th>
                                        <th width="30%">Đánh giá từ AI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($activeOrders as $order)
                                        <tr>
                                            <td><strong>{{ $order->name ?? 'Đơn hàng #' . $order->id }}</strong></td>

                                            <td style="vertical-align: middle;">
                                                <div class="progress"
                                                    style="margin-bottom: 5px; height: 18px; background-color: #f0f0f0; border-radius: 4px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);">

                                                    <div class="progress-bar {{ $order->bar_color }}" role="progressbar"
                                                        style="width: {{ $order->percent_complete }}%; line-height: 18px; font-weight: bold;">
                                                        @if ($order->percent_complete > 10)
                                                            {{ round($order->percent_complete) }}%
                                                        @endif
                                                    </div>

                                                    @if ($order->percent_planned > $order->percent_complete && $order->spi_status != 'Quá hạn')
                                                        <div class="progress-bar progress-bar-striped active"
                                                            role="progressbar"
                                                            style="width: {{ $order->percent_planned - $order->percent_complete }}%; background-color: rgba(0, 0, 0, 0.15); box-shadow: none;">
                                                        </div>
                                                    @endif
                                                </div>
                                                <div
                                                    style="display: flex; justify-content: space-between; font-size: 11px; color: #888;">
                                                    <span>Tiến độ: {{ $order->completed_tasks }}/{{ $order->total_tasks }}
                                                        việc</span>
                                                    <span>Kỳ vọng: {{ round($order->percent_planned) }}%</span>
                                                </div>
                                            </td>

                                            <td class="text-center" style="vertical-align: middle;">
                                                <span
                                                    style="font-weight: bold; font-size: 15px; color: {{ $order->spi < 1 ? '#e3342f' : '#38c172' }};">
                                                    {{ number_format($order->spi, 2) }}
                                                </span>
                                            </td>

                                            <td class="text-center" style="vertical-align: middle;">
                                                @if ($order->spi_status == 'hoanthanh' && $order->status !== 'completed')
                                                    <form action="{{ route('admin.orders.complete', $order->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Xác nhận chuyển trạng thái đơn hàng sang đã hoàn thành (đơn hàng sẽ không xuất hiện tại bảng theo dõi tiến độ đơn hàng đang thi công nữa) ?');">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-success btn-sm"
                                                            style="padding: 6px 10px; font-size: 12px; margin: 0;">
                                                            <i class="voyager-check"></i> Xác nhận Hoàn thành
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="label {{ $order->label_color }}"
                                                        style="padding: 6px 10px; font-size: 12px;">
                                                        {{ $order->spi_status }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td style="vertical-align: middle;" class="ai-insight-cell"
                                                data-order-id="{{ $order->id }}">
                                                <div
                                                    style="padding: 10px; text-align: center; color: #888; font-size: 12px; font-style: italic;">
                                                    <style>
                                                        @keyframes pulse {
                                                            0% {
                                                                opacity: 0.5;
                                                            }

                                                            50% {
                                                                opacity: 1;
                                                            }

                                                            100% {
                                                                opacity: 0.5;
                                                            }
                                                        }

                                                        .ai-loading {
                                                            animation: pulse 1.5s infinite;
                                                            color: #3490dc;
                                                            font-weight: bold;
                                                        }
                                                    </style>
                                                    <span class="ai-loading">
                                                        <i class="voyager-wand"></i> AI đang phân tích...
                                                    </span>
                                                </div>
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="padding: 50px 20px; text-align: center; color: #888; background-color: #fafafa;">
                            <i class="voyager-smile"
                                style="font-size: 48px; color: #ddd; margin-bottom: 15px; display: inline-block;"></i>
                            <h4 style="font-weight: bold; color: #555;">Không có đơn hàng nào đang thi công</h4>
                            <p style="margin: 0; font-size: 14px;">Tất cả đơn hàng đã được hoàn thành hoặc xưởng đang chờ
                                việc mới.</p>
                        </div>
                    @endif

                </div>
            </div>

            <div class="panel panel-info" style="border-color: #3490dc; box-shadow: 0 4px 10px rgba(52, 144, 220, 0.1);">
                <div class="panel-heading" style="background-color: #3490dc; color: white;">
                    <h3 class="panel-title" style="font-weight: bold;">
                        <i class="voyager-bell"></i> YÊU CẦU CHỜ NGHIỆM THU
                        @if (isset($totalPending) && $totalPending > 0)
                            : CÓ {{ $totalPending }} YÊU CẦU!
                        @endif
                    </h3>
                </div>
                <div class="panel-body" style="padding: 0;">
                    @if (isset($totalPending) && $totalPending > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" style="margin-bottom: 0;">
                                <thead style="background-color: #f0f8ff;">
                                    <tr>
                                        <th>Loại việc</th>
                                        <th>Tên công việc</th>
                                        <th>Người yêu cầu</th>
                                        <th>Ngày xin duyệt</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingTasks as $task)
                                        <tr>
                                            <td><span class="label label-warning">Thi công</span></td>
                                            <td><strong>{{ $task->name }}</strong></td>
                                            <td>{{ $task->worker->name ?? 'N/A' }}</td>
                                            <td>{{ $task->updated_at->format('H:i d/m/Y') }}</td>
                                            <td>
                                                <a href="{{ route('voyager.tasks.edit', $task->id) }}"
                                                    class="btn btn-sm btn-primary" style="margin:0;">Xem</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="padding: 40px 20px; text-align: center; background-color: #f8fafc;">
                            <i class="voyager-mug"
                                style="font-size: 48px; color: #a0aec0; margin-bottom: 15px; display: inline-block;"></i>
                            <h4 style="font-weight: bold; color: #4a5568;">Hiện tại không có yêu cầu nào cần duyệt!</h4>
                            <p style="margin: 0; font-size: 14px; color: #6c757d;">Bạn có thể thư giãn hoặc kiểm tra tiến
                                độ các công việc đang thi công.</p>
                        </div>
                    @endif

                </div>
            </div>

            <div class="panel panel-primary"
                style="border-color: #8b5cf6; box-shadow: 0 4px 10px rgba(139, 92, 246, 0.1);">
                <div class="panel-heading" style="background-color: #8b5cf6; color: white;">
                    <h3 class="panel-title" style="font-weight: bold;">
                        <i class="voyager-dollar"></i> PHIẾU YÊU CẦU ỨNG TIỀN/LƯƠNG CHỜ DUYỆT
                        @if (isset($totalPendingPayrolls) && $totalPendingPayrolls > 0)
                            : CÓ {{ $totalPendingPayrolls }} YÊU CẦU!
                        @endif
                    </h3>
                </div>
                <div class="panel-body" style="padding: 0;">
                    @if (isset($totalPendingPayrolls) && $totalPendingPayrolls > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" style="margin-bottom: 0;">
                                <thead style="background-color: #f5f3ff;">
                                    <tr>
                                        <th>Nhân viên</th>
                                        <th class="text-center">Kỳ lương</th>
                                        <th class="text-right">Số tiền xin ứng</th>
                                        <th class="text-center">Ngày gửi</th>
                                        <th class="text-center">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingPayrolls as $payroll)
                                        <tr>
                                            <td><strong>{{ $payroll->worker->name ?? 'Đã xóa' }}</strong></td>
                                            <td class="text-center">Tháng {{ $payroll->month }}/{{ $payroll->year }}</td>
                                            <td class="text-right"
                                                style="color: #d97706; font-weight: bold; font-size: 15px;">
                                                {{ number_format($payroll->withdrawn_amount) }} đ
                                            </td>
                                            <td class="text-center">{{ $payroll->created_at->format('H:i d/m/Y') }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('voyager.monthly-payrolls.show', $payroll->id) }}"
                                                    class="btn btn-sm btn-primary" style="margin:0;">Xem / Duyệt</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="padding: 40px 20px; text-align: center; background-color: #f8fafc;">
                            <i class="voyager-wallet"
                                style="font-size: 48px; color: #a0aec0; margin-bottom: 15px; display: inline-block;"></i>
                            <h4 style="font-weight: bold; color: #4a5568;">Không có yêu cầu ứng tiền nào cần duyệt!</h4>
                            <p style="margin: 0; font-size: 14px; color: #6c757d;">Tiền trong két vẫn còn nguyên vẹn.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="panel panel-danger" style="border-color: #e3342f; box-shadow: 0 4px 10px rgba(227, 52, 47, 0.1);">
                <div class="panel-heading" style="background-color: #e3342f; color: white;">
                    <h3 class="panel-title" style="font-weight: bold;">
                        <i class="voyager-warning"></i> CẢNH BÁO TRỄ HẠN
                        @if (isset($totalOverdue) && $totalOverdue > 0)
                            : CÓ {{ $totalOverdue }} CÔNG VIỆC!
                        @endif
                    </h3>
                </div>
                <div class="panel-body" style="padding: 0;">
                    @if (isset($totalOverdue) && $totalOverdue > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" style="margin-bottom: 0;">
                                <thead style="background-color: #fef2f2;">
                                    <tr>
                                        <th>Loại việc</th>
                                        <th>Tên công việc</th>
                                        <th>Người phụ trách</th>
                                        <th>Deadline</th>
                                        <th>Tình trạng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($overdueOrders as $order)
                                        <tr>
                                            <td><span class="label label-primary">Thiết kế</span></td>
                                            <td><strong>{{ $order->name }}</strong></td>
                                            <td>{{ $order->designer->name ?? 'Chưa giao' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($order->deadline)->format('H:i d/m/Y') }}</td>
                                            <td style="color: #e3342f; font-weight: bold;">
                                                @php
                                                    $deadline = \Carbon\Carbon::parse($order->deadline);
                                                    $days = $deadline->diffInDays(now());
                                                    $hours = $deadline->diffInHours(now()) % 24;
                                                @endphp
                                                Trễ {{ $days > 0 ? $days . ' ngày ' : '' }}{{ $hours }} giờ
                                            </td>
                                        </tr>
                                    @endforeach

                                    @foreach ($overdueTasks as $task)
                                        <tr>
                                            <td><span class="label label-warning">Thi công</span></td>
                                            <td><strong>{{ $task->name }}</strong></td>
                                            <td>{{ $task->worker->name ?? 'Chưa giao' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($task->deadline)->format('H:i d/m/Y') }}</td>
                                            <td style="color: #e3342f; font-weight: bold;">
                                                @php
                                                    $deadline = \Carbon\Carbon::parse($task->deadline);
                                                    $days = $deadline->diffInDays(now());
                                                    $hours = $deadline->diffInHours(now()) % 24;
                                                @endphp
                                                Trễ {{ $days > 0 ? $days . ' ngày ' : '' }}{{ $hours }} giờ
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="padding: 40px 20px; text-align: center; background-color: #f4fdf8;">
                            <i class="voyager-check"
                                style="font-size: 48px; color: #38c172; margin-bottom: 15px; display: inline-block;"></i>
                            <h4 style="font-weight: bold; color: #28a745;">Không có công việc nào bị trễ hạn.</h4>
                            <p style="margin: 0; font-size: 14px; color: #6c757d;">Tất cả các bộ phận đều đang bám sát tiến
                                độ đã đề ra.</p>
                        </div>
                    @endif

                </div>
            </div>

            <div class="panel panel-warning"
                style="border-color: #f59e0b; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.1);">
                <div class="panel-heading" style="background-color: #f59e0b; color: white;">
                    <h3 class="panel-title" style="font-weight: bold;">
                        <i class="voyager-clock"></i> CÔNG VIỆC SẮP ĐẾN HẠN (TRONG 48H)
                        @if (isset($totalUpcoming) && $totalUpcoming > 0)
                            : CÓ {{ $totalUpcoming }} VIỆC!
                        @endif
                    </h3>
                </div>
                <div class="panel-body" style="padding: 0;">
                    @if (isset($totalUpcoming) && $totalUpcoming > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" style="margin-bottom: 0;">
                                <thead style="background-color: #fffbeb;">
                                    <tr>
                                        <th>Loại việc</th>
                                        <th>Tên công việc</th>
                                        <th>Người phụ trách</th>
                                        <th>Deadline</th>
                                        <th>Thời gian còn lại</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($upcomingOrders as $order)
                                        <tr>
                                            <td><span class="label label-primary">Thiết kế</span></td>
                                            <td><strong>{{ $order->name }}</strong></td>
                                            <td>{{ $order->designer->name ?? 'Chưa giao' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($order->deadline)->format('H:i d/m/Y') }}</td>
                                            <td style="color: #d97706; font-weight: bold;">
                                                @php
                                                    $deadline = \Carbon\Carbon::parse($order->deadline);
                                                    $days = now()->diffInDays($deadline);
                                                    $hours = now()->diffInHours($deadline) % 24;
                                                @endphp
                                                Còn {{ $days > 0 ? $days . ' ngày ' : '' }}{{ $hours }} giờ
                                            </td>
                                        </tr>
                                    @endforeach

                                    @foreach ($upcomingTasks as $task)
                                        <tr>
                                            <td><span class="label label-warning">Thi công</span></td>
                                            <td><strong>{{ $task->name }}</strong></td>
                                            <td>{{ $task->worker->name ?? 'Chưa giao' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($task->deadline)->format('H:i d/m/Y') }}</td>
                                            <td style="color: #d97706; font-weight: bold;">
                                                @php
                                                    $deadline = \Carbon\Carbon::parse($task->deadline);
                                                    $days = now()->diffInDays($deadline);
                                                    $hours = now()->diffInHours($deadline) % 24;
                                                @endphp
                                                Còn {{ $days > 0 ? $days . ' ngày ' : '' }}{{ $hours }} giờ
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="padding: 40px 20px; text-align: center; background-color: #f8fafc;">
                            <i class="voyager-paper-plane"
                                style="font-size: 48px; color: #6cb2eb; margin-bottom: 15px; display: inline-block;"></i>
                            <h4 style="font-weight: bold; color: #3490dc;">Chưa có công việc nào cần gấp!</h4>
                            <p style="margin: 0; font-size: 14px; color: #6c757d;">Tất cả các công việc hiện tại đều còn dư
                                dả thời gian trên 48 tiếng.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            fetch("{{ route('voyager.ai_insights') }}")
                .then(response => response.json())
                .then(data => {
                    if (data.error) throw new Error("API Lỗi");


                    const insights = {};
                    data.forEach(item => {
                        let actualId = item.order_id || item.id;

                        if (actualId) {
                            insights[actualId.toString()] = item;
                        }
                    });

                    document.querySelectorAll('.ai-insight-cell').forEach(cell => {
                        const orderId = cell.getAttribute('data-order-id').toString();

                        if (insights[orderId]) {
                            const insight = insights[orderId];
                            let badgeClass = 'default';
                            let mucDo = (insight.muc_do_canh_bao || '').toLowerCase();

                            if (mucDo.includes('cao')) badgeClass = 'danger';
                            else if (mucDo.includes('trung bình')) badgeClass = 'warning';
                            else if (mucDo.includes('an toàn')) badgeClass = 'success';

                            cell.innerHTML = `
                            <span class="label label-${badgeClass}" style="padding: 4px 8px; font-size: 11px; display: inline-block; margin-bottom: 5px;">
                                <i class="voyager-lightbulb"></i> AI Cảnh báo: ${insight.muc_do_canh_bao.toUpperCase()}
                            </span>
                            <div style="font-size: 12px; color: #555; line-height: 1.4; background: #f8fafc; padding: 6px; border-radius: 4px; border-left: 3px solid #3490dc;">
                                <i>"${insight.ly_do_suy_luan}"</i>
                            </div>
                        `;
                        } else {
                            cell.innerHTML =
                                `<span class="text-muted" style="font-size: 11px; font-style: italic;"><i class="voyager-check"></i> AI đang bị quá tải</span>`;
                        }
                    });
                })
                .catch(error => {
                    console.error('Lỗi tải AI:', error);
                    document.querySelectorAll('.ai-insight-cell').forEach(cell => {
                        cell.innerHTML =
                            `<span class="text-danger" style="font-size: 11px;"><i class="voyager-x"></i> Không thể kết nối AI lúc này</span>`;
                    });
                });
        });
    </script>
@stop
