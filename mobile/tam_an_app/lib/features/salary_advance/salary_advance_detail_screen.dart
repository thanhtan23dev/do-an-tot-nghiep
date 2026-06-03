import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'salary_advance_provider.dart';

class SalaryAdvanceDetailScreen extends ConsumerWidget {
  final int id;
  const SalaryAdvanceDetailScreen({super.key, required this.id});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detailState = ref.watch(salaryAdvanceDetailProvider(id));
    final formatMoney = NumberFormat.decimalPattern('vi_VN');

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Chi tiết phiếu lương', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 0,
        centerTitle: true,
      ),
      body: detailState.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, stack) => Center(child: Text(err.toString().replaceAll('Exception: ', ''), style: const TextStyle(color: Colors.red))),
        data: (payroll) {
          final isPaid = payroll['status'] == 'paid';
          final earned = double.tryParse(payroll['earned_amount']?.toString() ?? '0') ?? 0;
          final withdrawn = double.tryParse(payroll['withdrawn_amount']?.toString() ?? '0') ?? 0;
          final deductionAmount = double.tryParse(payroll['deduction_amount']?.toString() ?? '0') ?? 0;
          final String deductionNote = payroll['deduction_note'] ?? '';
          final List tasks = payroll['tasks'] ?? [];

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Thẻ Thông tin chính
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(24), border: Border.all(color: Colors.grey.shade200)),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Phiếu Lương & Ứng Tiền', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                              const SizedBox(height: 4),
                              Text('Kỳ lương: Tháng ${payroll['month']} năm ${payroll['year']}', style: const TextStyle(fontSize: 13, color: Colors.grey, fontWeight: FontWeight.w500)),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                            decoration: BoxDecoration(color: isPaid ? Colors.green.shade50 : Colors.orange.shade50, borderRadius: BorderRadius.circular(20), border: Border.all(color: isPaid ? Colors.green.shade200 : Colors.orange.shade200)),
                            child: Text(isPaid ? '✓ Đã duyệt' : '⧗ Đang chờ', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: isPaid ? Colors.green.shade700 : Colors.orange.shade700)),
                          ),
                        ],
                      ),
                      const SizedBox(height: 24),
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(color: Colors.grey.shade50, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.grey.shade200)),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Tổng nghiệm thu trong kỳ', style: TextStyle(fontSize: 12, color: Colors.grey, fontWeight: FontWeight.w600)),
                            Text('+ ${formatMoney.format(earned)} VNĐ', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.black87)),
                          ],
                        ),
                      ),
                      const SizedBox(height: 12),
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(color: Colors.blue.shade50, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.blue.shade100)),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Số tiền bạn yêu cầu ứng', style: TextStyle(fontSize: 12, color: Colors.blue.shade600, fontWeight: FontWeight.w600)),
                            Text('- ${formatMoney.format(withdrawn)} VNĐ', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w900, color: Colors.blue.shade700)),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),

                // Khối Khấu trừ (Sếp chú thích)
                if (deductionAmount > 0 || deductionNote.isNotEmpty) ...[
                  const SizedBox(height: 16),
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(24), border: Border.all(color: Colors.red.shade100)),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('PHẢN HỒI TỪ QUẢN LÝ', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.red.shade600, letterSpacing: 1)),
                        const SizedBox(height: 12),
                        if (deductionAmount > 0) ...[
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text('Khoản khấu trừ thêm:', style: TextStyle(fontSize: 13, color: Colors.red.shade800, fontWeight: FontWeight.w500)),
                              Text('- ${formatMoney.format(deductionAmount)} VNĐ', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.red.shade600)),
                            ],
                          ),
                          const Padding(padding: EdgeInsets.symmetric(vertical: 8), child: Divider(height: 1)),
                        ],
                        if (deductionNote.isNotEmpty) ...[
                          Text('Ghi chú:', style: TextStyle(fontSize: 13, color: Colors.red.shade800, fontWeight: FontWeight.w500)),
                          const SizedBox(height: 4),
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(color: Colors.white.withOpacity(0.5), borderRadius: BorderRadius.circular(8)),
                            child: Text(deductionNote, style: TextStyle(fontSize: 13, fontStyle: FontStyle.italic, color: Colors.red.shade700)),
                          ),
                        ]
                      ],
                    ),
                  ),
                ],

                const SizedBox(height: 24),
                const Text('CÔNG VIỆC ĐÃ NGHIỆM THU TRONG PHIẾU', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey, letterSpacing: 1)),
                const SizedBox(height: 12),

                if (tasks.isEmpty)
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.grey.shade200)),
                    child: const Text('Không có công việc cụ thể nào được đính kèm, phiếu này chỉ lưu yêu cầu ứng tiền.', style: TextStyle(fontSize: 13, color: Colors.grey, fontStyle: FontStyle.italic), textAlign: TextAlign.center),
                  )
                else
                  ...tasks.map((task) {
                    final taskReward = double.tryParse(task['reward']?.toString() ?? '0') ?? 0;
                    final String taskDate = DateFormat('dd/MM/yyyy').format(DateTime.parse(task['updated_at']).toLocal());
                    return Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.grey.shade200)),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(task['name'] ?? 'Công việc #${task['id']}', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                                const SizedBox(height: 4),
                                Text(taskDate, style: const TextStyle(fontSize: 12, color: Colors.grey)),
                              ],
                            ),
                          ),
                          Text('+ ${formatMoney.format(taskReward)} đ', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: Colors.black87)),
                        ],
                      ),
                    );
                  })
              ],
            ),
          );
        },
      ),
    );
  }
}