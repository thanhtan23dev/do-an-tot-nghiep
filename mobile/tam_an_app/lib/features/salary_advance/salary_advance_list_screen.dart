import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'salary_advance_provider.dart';
import 'salary_advance_detail_screen.dart';
import 'salary_advance_create_screen.dart';

class SalaryAdvanceListScreen extends ConsumerWidget {
  const SalaryAdvanceListScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final advancesState = ref.watch(salaryAdvancesProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      // appBar: AppBar(
      //   title: const Text('Lịch sử phiếu lương & ứng tiền', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
      //   backgroundColor: Colors.white,
      //   foregroundColor: Colors.black87,
      //   elevation: 0,
      //   centerTitle: true,
      // ),
      // NÚT TẠO YÊU CẦU NỔI (FAB)
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          Navigator.push(context, MaterialPageRoute(builder: (context) => const SalaryAdvanceCreateScreen()))
              .then((_) => ref.refresh(salaryAdvancesProvider));
        },
        backgroundColor: Colors.blue.shade600,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Tạo yêu cầu', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
      ),
      body: advancesState.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, stack) => Center(
          child: Text(err.toString().replaceAll('Exception: ', ''), style: const TextStyle(color: Colors.red)),
        ),
        data: (payrolls) {
          if (payrolls.isEmpty) {
            return RefreshIndicator(
              onRefresh: () async => ref.refresh(salaryAdvancesProvider),
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                children: [
                  SizedBox(
                    height: MediaQuery.of(context).size.height * 0.6,
                    child: Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.account_balance_wallet_outlined, size: 64, color: Colors.blue.shade200),
                          const SizedBox(height: 16),
                          const Text('Bạn chưa có phiếu lương nào', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.grey)),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            );
          }

          return RefreshIndicator(
            onRefresh: () async => ref.refresh(salaryAdvancesProvider),
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              physics: const AlwaysScrollableScrollPhysics(),
              itemCount: payrolls.length,
              separatorBuilder: (context, index) => const SizedBox(height: 16),
              itemBuilder: (context, index) {
                final payroll = payrolls[index];
                final isPaid = payroll['status'] == 'paid';
                final String date = DateFormat('dd/MM/yyyy').format(DateTime.parse(payroll['created_at']).toLocal());

                final earned = double.tryParse(payroll['earned_amount']?.toString() ?? '0') ?? 0;
                final withdrawn = double.tryParse(payroll['withdrawn_amount']?.toString() ?? '0') ?? 0;
                final formatMoney = NumberFormat.decimalPattern('vi_VN');

                return InkWell(
                  onTap: () {
                    Navigator.push(context, MaterialPageRoute(builder: (context) => SalaryAdvanceDetailScreen(id: payroll['id'])));
                  },
                  borderRadius: BorderRadius.circular(20),
                  child: Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: Colors.grey.shade200),
                      boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 10, offset: const Offset(0, 4))],
                    ),
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
                                Text('Tháng ${payroll['month']}/${payroll['year']}', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                                const SizedBox(height: 4),
                                Text('Ngày tạo: $date', style: const TextStyle(fontSize: 12, color: Colors.grey)),
                              ],
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: isPaid ? Colors.green.shade50 : Colors.orange.shade50,
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(color: isPaid ? Colors.green.shade200 : Colors.orange.shade200),
                              ),
                              child: Text(
                                isPaid ? 'Đã duyệt' : 'Đang chờ',
                                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: isPaid ? Colors.green.shade700 : Colors.orange.shade700),
                              ),
                            ),
                          ],
                        ),
                        const Padding(padding: EdgeInsets.symmetric(vertical: 12), child: Divider(height: 1)),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Nghiệm thu trong tháng:', style: TextStyle(fontSize: 13, color: Colors.grey)),
                            Text('${formatMoney.format(earned)} đ', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.black87)),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Đã xin ứng:', style: TextStyle(fontSize: 13, color: Colors.grey)),
                            Text('${formatMoney.format(withdrawn)} đ', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.blue.shade600)),
                          ],
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}