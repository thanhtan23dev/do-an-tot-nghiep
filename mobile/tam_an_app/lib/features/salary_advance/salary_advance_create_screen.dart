import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'salary_advance_provider.dart';
import 'salary_advance_repository.dart';

class SalaryAdvanceCreateScreen extends ConsumerStatefulWidget {
  const SalaryAdvanceCreateScreen({super.key});

  @override
  ConsumerState<SalaryAdvanceCreateScreen> createState() => _SalaryAdvanceCreateScreenState();
}

class _SalaryAdvanceCreateScreenState extends ConsumerState<SalaryAdvanceCreateScreen> {
  final TextEditingController _amountController = TextEditingController();
  bool _isSubmitting = false;

  Future<void> _submitRequest(double availableBalance) async {
    final String amountStr = _amountController.text.replaceAll(',', '').replaceAll('.', '');
    final double amount = double.tryParse(amountStr) ?? 0;

    if (amount < 10000) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Số tiền muốn ứng tối thiểu là 10,000 VNĐ.'), backgroundColor: Colors.red));
      return;
    }
    if (amount > availableBalance) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Số tiền ứng vượt quá số dư khả dụng.'), backgroundColor: Colors.red));
      return;
    }

    setState(() => _isSubmitting = true);
    try {
      await ref.read(salaryAdvanceRepositoryProvider).submitAdvance(amount);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Gửi yêu cầu ứng lương thành công!'), backgroundColor: Colors.green));
        Navigator.pop(context); // Quay lại màn hình danh sách
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString().replaceAll('Exception: ', '')), backgroundColor: Colors.red));
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final infoState = ref.watch(salaryAdvanceInfoProvider);
    final formatMoney = NumberFormat.decimalPattern('vi_VN');

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Ứng tiền mặt', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 0,
        centerTitle: true,
      ),
      body: infoState.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, stack) => Center(child: Text(err.toString(), style: const TextStyle(color: Colors.red))),
        data: (info) {
          final totalBalance = double.tryParse(info['total_balance']?.toString() ?? '0') ?? 0;
          final pendingAmount = double.tryParse(info['pending_amount']?.toString() ?? '0') ?? 0;
          final availableBalance = double.tryParse(info['available_balance']?.toString() ?? '0') ?? 0;

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Khung hiển thị Két sắt
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [Colors.blue.shade700, Colors.blue.shade900]),
                    borderRadius: BorderRadius.circular(24),
                    boxShadow: [BoxShadow(color: Colors.blue.shade500.withOpacity(0.3), blurRadius: 15, offset: const Offset(0, 8))],
                  ),
                  child: Column(
                    children: [
                      const Icon(Icons.account_balance_wallet, color: Colors.white70, size: 32),
                      const SizedBox(height: 8),
                      const Text('SỐ DƯ KHẢ DỤNG', style: TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.bold, letterSpacing: 1)),
                      const SizedBox(height: 8),
                      Text('${formatMoney.format(availableBalance)} đ', style: const TextStyle(color: Colors.white, fontSize: 32, fontWeight: FontWeight.w900)),

                      const SizedBox(height: 24),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Tổng két sắt', style: TextStyle(color: Colors.white70, fontSize: 11)),
                              Text('${formatMoney.format(totalBalance)} đ', style: const TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.bold)),
                            ],
                          ),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              const Text('Đang chờ duyệt', style: TextStyle(color: Colors.white70, fontSize: 11)),
                              Text('${formatMoney.format(pendingAmount)} đ', style: const TextStyle(color: Colors.orangeAccent, fontSize: 14, fontWeight: FontWeight.bold)),
                            ],
                          ),
                        ],
                      )
                    ],
                  ),
                ),

                const SizedBox(height: 32),
                const Text('NHẬP SỐ TIỀN MUỐN ỨNG', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey, letterSpacing: 1)),
                const SizedBox(height: 8),

                // Form nhập tiền
                TextField(
                  controller: _amountController,
                  keyboardType: TextInputType.number,
                  inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                  style: TextStyle(fontSize: 24, fontWeight: FontWeight.w900, color: Colors.blue.shade700),
                  decoration: InputDecoration(
                    hintText: '0',
                    suffixText: 'VNĐ',
                    suffixStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.grey),
                    filled: true,
                    fillColor: Colors.white,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(20), borderSide: BorderSide.none),
                    focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(20), borderSide: BorderSide(color: Colors.blue.shade300, width: 2)),
                  ),
                ),

                const SizedBox(height: 24),
                SizedBox(
                  width: double.infinity,
                  height: 56,
                  child: ElevatedButton(
                    onPressed: _isSubmitting ? null : () => _submitRequest(availableBalance),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.blue.shade600,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    ),
                    child: _isSubmitting
                        ? const CircularProgressIndicator(color: Colors.white)
                        : const Text('GỬI YÊU CẦU ỨNG TIỀN', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.white, letterSpacing: 1)),
                  ),
                )
              ],
            ),
          );
        },
      ),
    );
  }
}