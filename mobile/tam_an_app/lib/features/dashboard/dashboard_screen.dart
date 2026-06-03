import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'dashboard_provider.dart';

class DashboardScreen extends ConsumerWidget {
  const DashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final dashboardState = ref.watch(dashboardProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: dashboardState.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.error_outline, color: Colors.red.shade300, size: 48),
              const SizedBox(height: 16),
              Text('Lỗi: ${error.toString().replaceAll('Exception: ', '')}', textAlign: TextAlign.center, style: const TextStyle(color: Colors.red)),
              TextButton(
                onPressed: () => ref.refresh(dashboardProvider),
                child: const Text('Thử lại'),
              )
            ],
          ),
        ),
        data: (payload) {
          final data = payload.containsKey('stats') ? payload : payload['data'];
          final stats = data['stats'] ?? {};
          final List nearDeadlines = data['near_deadlines'] ?? [];

          return RefreshIndicator(
            onRefresh: () async => ref.refresh(dashboardProvider),
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // --- HEADER: LỜI CHÀO ---
                  const Text(
                    'Chào bạn! 👋',
                    style: TextStyle(fontSize: 28, fontWeight: FontWeight.w900, color: Color(0xFF1F2937), letterSpacing: -0.5),
                  ),
                  const SizedBox(height: 4),
                  const Text(
                    'Hôm nay bạn muốn hoàn thành thêm bao nhiêu việc?',
                    style: TextStyle(fontSize: 14, color: Color(0xFF6B7280), fontWeight: FontWeight.w500),
                  ),
                  const SizedBox(height: 24),

                  // --- THỐNG KÊ: CÔNG VIỆC (Nằm ngang) ---
                  Row(
                    children: [
                      Expanded(
                        child: _buildStatCard(
                          title: 'CÔNG VIỆC MỚI',
                          value: stats['new_jobs']?.toString() ?? '0',
                          icon: Icons.work_outline,
                          color: Colors.blue,
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: _buildStatCard(
                          title: 'ĐANG LÀM',
                          value: stats['active_jobs']?.toString() ?? '0',
                          icon: Icons.pending_actions,
                          color: Colors.purple,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // --- THỐNG KÊ: SỐ DƯ (Full width điểm nhấn) ---
                  _buildBalanceCard(stats['total_balance'] ?? 0),
                  const SizedBox(height: 32),

                  // --- LIST: CÔNG VIỆC GẤP (SẮP HẾT HẠN) ---
                  Container(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(color: Colors.grey.shade200),
                      boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 10, offset: const Offset(0, 4))],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Padding(
                          padding: const EdgeInsets.all(20.0),
                          child: Row(
                            children: [
                              Container(width: 8, height: 8, decoration: const BoxDecoration(color: Colors.red, shape: BoxShape.circle)),
                              const SizedBox(width: 8),
                              const Text('Công việc gấp (Sắp hết hạn)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF1F2937))),
                            ],
                          ),
                        ),
                        Divider(height: 1, color: Colors.grey.shade100),

                        if (nearDeadlines.isEmpty)
                          const Padding(
                            padding: EdgeInsets.all(32.0),
                            child: Center(
                              child: Text('Tuyệt vời! Không có công việc nào đang bị trễ hạn.', style: TextStyle(color: Colors.grey, fontStyle: FontStyle.italic, fontSize: 13)),
                            ),
                          )
                        else
                          ListView.separated(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: nearDeadlines.length,
                            separatorBuilder: (context, index) => Divider(height: 1, color: Colors.grey.shade50),
                            itemBuilder: (context, index) {
                              final job = nearDeadlines[index];
                              final DateTime deadline = DateTime.parse(job['deadline']).toLocal();
                              final formattedDeadline = DateFormat('HH:mm dd/MM').format(deadline);

                              return Padding(
                                padding: const EdgeInsets.all(20.0),
                                child: Row(
                                  children: [
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(job['name'], style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1F2937), fontSize: 15)),
                                          const SizedBox(height: 4),
                                          Text('Ngày hết hạn: $formattedDeadline', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.red.shade500)),
                                        ],
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.all(8),
                                      decoration: BoxDecoration(color: Colors.grey.shade100, borderRadius: BorderRadius.circular(8)),
                                      child: Icon(Icons.arrow_forward_ios, size: 16, color: Colors.grey.shade600),
                                    ),
                                  ],
                                ),
                              );
                            },
                          ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 40),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  // Widget Thẻ Thống Kê Nhỏ (Nửa màn hình)
  Widget _buildStatCard({required String title, required String value, required IconData icon, required MaterialColor color}) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 10, offset: const Offset(0, 4))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 44, height: 44,
            decoration: BoxDecoration(color: color.shade50, borderRadius: BorderRadius.circular(14)),
            child: Icon(icon, color: color.shade600, size: 22),
          ),
          const SizedBox(height: 16),
          Text(title, style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.grey.shade500, letterSpacing: 0.5), maxLines: 1, overflow: TextOverflow.ellipsis),
          const SizedBox(height: 4),
          Text(value, style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w900, color: Color(0xFF1F2937)), maxLines: 1, overflow: TextOverflow.ellipsis),
        ],
      ),
    );
  }

  // Widget Thẻ Số Dư Tổng (Full width, Màu Xanh)
  Widget _buildBalanceCard(dynamic amount) {
    final number = double.tryParse(amount.toString()) ?? 0;
    final formatter = NumberFormat.decimalPattern('vi_VN');
    final formattedBalance = formatter.format(number);

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.green.shade50.withOpacity(0.5),
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: Colors.green.shade100),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 44, height: 44,
            decoration: BoxDecoration(color: Colors.green.shade100, borderRadius: BorderRadius.circular(14)),
            child: Icon(Icons.account_balance_wallet_outlined, color: Colors.green.shade600, size: 22),
          ),
          const SizedBox(height: 16),
          Text('TỔNG SỐ DƯ HIỆN TẠI', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.green.shade600, letterSpacing: 0.5)),
          const SizedBox(height: 4),
          Text('+$formattedBalance đ', style: TextStyle(fontSize: 28, fontWeight: FontWeight.w900, color: Colors.green.shade700)),
        ],
      ),
    );
  }
}