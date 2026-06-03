<table>
    <thead>
        <tr>
            <th colspan="7" style="text-align: center; font-weight: bold; font-size: 14px;">
                BẢNG TỔNG HỢP - {{ mb_strtoupper($employee->name) }}
            </th>
        </tr>
        <tr>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">STT</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">THÁNG</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Giá trị nghiệm thu</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Giá trị đã tạm ứng theo tháng</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Tổng số tiền trừ</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Ghi chú trừ</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Phần còn lại cần phải Thanh toán
            </th>
        </tr>
    </thead>
    <tbody>
        @php
            $sumEarned = 0;
            $sumWithdrawn = 0;
            $sumDeduction = 0;
        @endphp

        @foreach ($payrolls as $index => $row)
            @php
                $sumEarned += $row->earned_amount;
                $sumWithdrawn += $row->withdrawn_amount;
                $sumDeduction += $row->deduction_amount;
            @endphp
            <tr>
                <td style="text-align: center; border: 1px solid #000;">{{ $index + 1 }}</td>
                <td style="text-align: center; border: 1px solid #000;">
                    {{ str_pad($row->month, 2, '0', STR_PAD_LEFT) }}/{{ $row->year }}</td>
                <td style="border: 1px solid #000;">{{ $row->earned_amount }}</td>
                <td style="border: 1px solid #000;">{{ $row->withdrawn_amount }}</td>
                <td style="border: 1px solid #000;">{{ $row->deduction_amount }}</td>
                <td style="border: 1px solid #000;">{{ $row->deduction_note }}</td>
                <td style="border: 1px solid #000;"></td>
            </tr>
        @endforeach

        <tr>
            <td colspan="2"
                style="font-weight: bold; text-align: center; border: 1px solid #000; background-color: #fce4d6;">
                TỔNG CỘNG
            </td>
            <td style="font-weight: bold; border: 1px solid #000; background-color: #fce4d6;">{{ $sumEarned }}</td>
            <td style="font-weight: bold; border: 1px solid #000; background-color: #fce4d6;">{{ $sumWithdrawn }}</td>
            <td style="font-weight: bold; border: 1px solid #000; background-color: #fce4d6;">{{ $sumDeduction }}</td>
            <td style="border: 1px solid #000; background-color: #fce4d6;"></td>
            <td
                style="font-weight: bold; text-align: right; background-color: #FFFF00; color: #000000; border: 1px solid #000;">
                {{ $employee->total_balance }}
            </td>
        </tr>
    </tbody>
</table>
