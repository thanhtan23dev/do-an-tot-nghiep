<table style="font-family: 'Times New Roman', Times, serif;">
    {{-- PHẦN HEADER BÁO CÁO --}}
    <tr>
        <td colspan="9" style="text-align: center; font-weight: bold; font-size: 16px;">BIÊN BẢN NGHIỆM THU</td>
    </tr>
    <tr>
        <td colspan="9" style="text-align: center; font-weight: bold; font-size: 14px;">
            SỐ LƯỢNG VÀ GIÁ TRỊ THỰC HIỆN (Tháng
            {{ str_pad($payroll->month, 2, '0', STR_PAD_LEFT) }}/{{ $payroll->year }})
        </td>
    </tr>
    <tr>
        <td colspan="9" style="font-style: italic;">
            Hôm nay, ngày ..... tháng ..... năm {{ $payroll->year }} Chúng tôi gồm:
        </td>
    </tr>
    <tr>
        <td colspan="4">1: Ông ..............................................................</td>
        <td colspan="5">Chức vụ: Trưởng phòng Kinh Doanh</td>
    </tr>
    <tr>
        <td colspan="4">2: Ông ..............................................................</td>
        <td colspan="5">Chức vụ: Giám Sát</td>
    </tr>
    <tr>
        {{-- Tự động điền tên nhân viên của phiếu lương này --}}
        <td colspan="4">3: Ông {{ $employee->name }}</td>
        <td colspan="5">Chức vụ: Nhân viên sản xuất</td>
    </tr>
    <tr>
        <td colspan="9">Chúng tôi thống nhất cùng nhau xác nhận số lượng, giá trị hàng hóa theo bảng kê như sau:</td>
    </tr>
    <tr>
        <td colspan="9"></td>
    </tr> {{-- Dòng trống --}}

    {{-- PHẦN BẢNG DỮ LIỆU --}}
    <thead>
        <tr>
            <th rowspan="2"
                style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000;">STT</th>
            <th rowspan="2"
                style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000;">NỘI DUNG
            </th>
            <th colspan="3" style="font-weight: bold; text-align: center; border: 1px solid #000;">Quy cách (mm)</th>
            <th rowspan="2"
                style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000;">Khối
                lượng<br>(S.Lượng)</th>
            <th rowspan="2"
                style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000;">ĐVT</th>
            <th rowspan="2"
                style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000;">Đơn
                giá<br>Sản xuất</th>
            <th rowspan="2"
                style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000;">Thành
                tiền<br>Sản xuất</th>
        </tr>
        <tr>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Dài</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Rộng</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Cao</th>
        </tr>
    </thead>
    <tbody>
        @if ($payrollTasks->isEmpty())
            <tr>
                <td colspan="9" style="text-align: center; font-style: italic; border: 1px solid #000;">Không có công
                    việc nào.</td>
            </tr>
        @else
            @php $tongThanhTien = 0; @endphp
            @foreach ($payrollTasks as $orderName => $tasks)
                {{-- Tiêu đề đơn hàng --}}
                <tr>
                    <td colspan="9" style="font-weight: bold; background-color: #e2e8f0; border: 1px solid #000;">
                        {{ $orderName }}
                    </td>
                </tr>

                {{-- Chi tiết công việc --}}
                @foreach ($tasks as $index => $task)
                    @php
                        $donGia = $task->stonePrice ? $task->stonePrice->price_per : 0;
                        $tongThanhTien += $task->reward ?? 0;
                    @endphp
                    <tr>
                        <td style="text-align: center; border: 1px solid #000;">{{ $index + 1 }}</td>
                        <td style="border: 1px solid #000;">{{ $task->name }}</td>
                        <td style="text-align: center; border: 1px solid #000;">{{ $task->length }}</td>
                        <td style="text-align: center; border: 1px solid #000;">{{ $task->width }}</td>
                        <td style="text-align: center; border: 1px solid #000;">{{ $task->height }}</td>
                        <td style="text-align: center; border: 1px solid #000;">
                            {{ $task->quantity ?? ($task->completed_count ?? 1) }}</td>
                        <td style="text-align: center; border: 1px solid #000;">{{ $task->unit }}</td>
                        <td style="text-align: right; border: 1px solid #000;">{{ $donGia > 0 ? $donGia : '' }}</td>
                        <td style="text-align: right; border: 1px solid #000;">{{ $task->reward ?? 0 }}</td>
                    </tr>
                @endforeach
            @endforeach

            {{-- Dòng Tổng Cộng --}}
            <tr>
                <td colspan="8" style="font-weight: bold; text-align: center; border: 1px solid #000;">TỔNG CỘNG</td>
                <td style="font-weight: bold; text-align: right; border: 1px solid #000;">{{ $tongThanhTien }}</td>
            </tr>
        @endif
    </tbody>
</table>
