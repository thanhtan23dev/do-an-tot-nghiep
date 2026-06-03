import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'completed_jobs_provider.dart';
import 'job_detail_screen.dart';

class CompletedJobsScreen extends ConsumerStatefulWidget {
  const CompletedJobsScreen({super.key});

  @override
  ConsumerState<CompletedJobsScreen> createState() => _CompletedJobsScreenState();
}

class _CompletedJobsScreenState extends ConsumerState<CompletedJobsScreen> {
  final TextEditingController _searchController = TextEditingController();

  @override
  Widget build(BuildContext context) {
    final jobsState = ref.watch(completedJobsProvider);
    final searchQuery = ref.watch(completedJobsSearchProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              TextField(
                controller: _searchController,
                onSubmitted: (value) => ref.read(completedJobsSearchProvider.notifier).state = value,
                decoration: InputDecoration(
                  hintText: 'Tìm tên công việc hoặc mã...',
                  prefixIcon: const Icon(Icons.search, color: Colors.grey),
                  suffixIcon: searchQuery.isNotEmpty
                      ? IconButton(
                    icon: const Icon(Icons.close, color: Colors.redAccent),
                    onPressed: () {
                      _searchController.clear();
                      ref.read(completedJobsSearchProvider.notifier).state = '';
                    },
                  )
                      : null,
                  filled: true,
                  fillColor: Colors.white,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                ),
              ),
              const SizedBox(height: 16),

              Expanded(
                child: jobsState.when(
                  loading: () => const Center(child: CircularProgressIndicator()),
                  error: (err, stack) => Center(
                    child: Text(
                      err.toString().replaceAll('Exception: ', ''),
                      style: const TextStyle(color: Colors.red, fontSize: 16),
                      textAlign: TextAlign.center,
                    ),
                  ),
                  data: (data) {
                    final bool isDesigner = data['is_designer'] ?? false;

                    if (isDesigner) {
                      final List orders = data['data']['orders']['data'] ?? [];
                      final List tasks = data['data']['tasks']['data'] ?? [];

                      if (orders.isEmpty && tasks.isEmpty) {
                        return _buildEmptyState(context, searchQuery);
                      }

                      return RefreshIndicator(
                        onRefresh: () async => ref.refresh(completedJobsProvider),
                        child: ListView(
                          physics: const AlwaysScrollableScrollPhysics(),
                          children: [
                            _buildSectionHeader('Thiết kế Đơn Hàng', Colors.purple),
                            if (orders.isEmpty)
                              _buildEmptySection('Chưa có Thiết kế Đơn hàng nào hoàn thành.')
                            else
                              ...orders.map((item) => Padding(
                                padding: const EdgeInsets.only(bottom: 16),
                                child: CompletedJobCard(item: item, type: 'order', isDesigner: true, ref: ref),
                              )),

                            const SizedBox(height: 24),

                            _buildSectionHeader('Thiết kế Công Việc', Colors.orange),
                            if (tasks.isEmpty)
                              _buildEmptySection('Chưa có Thiết kế Công việc nào hoàn thành.')
                            else
                              ...tasks.map((item) => Padding(
                                padding: const EdgeInsets.only(bottom: 16),
                                child: CompletedJobCard(item: item, type: 'task', isDesigner: true, ref: ref),
                              )),
                          ],
                        ),
                      );
                    } else {
                      final List items = data['data']['data'] ?? [];

                      if (items.isEmpty) {
                        return _buildEmptyState(context, searchQuery);
                      }

                      return RefreshIndicator(
                        onRefresh: () async => ref.refresh(completedJobsProvider),
                        child: ListView.builder(
                          physics: const AlwaysScrollableScrollPhysics(),
                          itemCount: items.length,
                          itemBuilder: (context, index) {
                            return Padding(
                              padding: const EdgeInsets.only(bottom: 16.0),
                              child: CompletedJobCard(item: items[index], type: 'task', isDesigner: false, ref: ref),
                            );
                          },
                        ),
                      );
                    }
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title, Color color) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        children: [
          Container(width: 12, height: 12, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
          const SizedBox(width: 8),
          Text(title.toUpperCase(), style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, letterSpacing: 0.5)),
        ],
      ),
    );
  }

  Widget _buildEmptySection(String message) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(color: Colors.grey.shade50, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.grey.shade200, style: BorderStyle.solid)),
      child: Center(
        child: Text(message, textAlign: TextAlign.center, style: TextStyle(color: Colors.grey.shade500, fontWeight: FontWeight.w500, fontSize: 13)),
      ),
    );
  }

  Widget _buildEmptyState(BuildContext context, String searchQuery) {
    return RefreshIndicator(
      onRefresh: () async => ref.refresh(completedJobsProvider),
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          SizedBox(
            height: MediaQuery.of(context).size.height * 0.6,
            child: Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.history_edu, size: 64, color: Colors.blue.shade200),
                  const SizedBox(height: 16),
                  Text(searchQuery.isNotEmpty ? 'Không tìm thấy kết quả' : 'Chưa có lịch sử công việc', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  Text('Vuốt xuống để tải lại', style: TextStyle(fontSize: 13, color: Colors.grey.shade400)),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class CompletedJobCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final String type;
  final bool isDesigner;
  final WidgetRef ref;

  const CompletedJobCard({
    super.key,
    required this.item,
    required this.type,
    required this.isDesigner,
    required this.ref,
  });

  @override
  Widget build(BuildContext context) {
    final isOrder = type == 'order';
    final String code = isOrder ? 'Mã Đơn: #${item['id']}' : 'Đơn: ${item['order']?['name'] ?? 'N/A'}';
    final double reward = double.tryParse(item['reward']?.toString() ?? '0') ?? 0;

    final bool hasPayroll = item['monthly_payroll_id'] != null;
    final String dateLabel = hasPayroll ? 'ĐÃ DUYỆT VÀO PHIẾU' : 'NGÀY XONG';
    final String formattedDate = hasPayroll
        ? 'PL-${item['monthly_payroll_id']}'
        : DateFormat('HH:mm d/M/y').format(DateTime.parse(item['updated_at']).toLocal());

    Color indicatorColor = isDesigner ? (isOrder ? Colors.purple : Colors.orange) : Colors.blue;

    return InkWell(
      onTap: () {
        Navigator.push(context, MaterialPageRoute(builder: (context) => JobDetailScreen(type: type, id: item['id'], isDesigner: isDesigner)));
      },
      borderRadius: BorderRadius.circular(20),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: Colors.grey.shade200),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.03), blurRadius: 10, offset: const Offset(0, 4))],
        ),
        child: Row(
          children: [
            Container(
                width: 6,
                constraints: const BoxConstraints(minHeight: 100),
                decoration: BoxDecoration(
                    color: indicatorColor,
                    borderRadius: const BorderRadius.only(topLeft: Radius.circular(20), bottomLeft: Radius.circular(20))
                )
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(code, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.grey)),
                              const SizedBox(height: 4),
                              Text(item['name'], style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold), maxLines: 2, overflow: TextOverflow.ellipsis),
                            ],
                          ),
                        ),
                        Container(
                            margin: const EdgeInsets.only(left: 8),
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(color: Colors.green.shade50, borderRadius: BorderRadius.circular(8)),
                            child: Text('HOÀN THÀNH', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.green.shade700))
                        ),
                      ],
                    ),
                    const Divider(height: 24),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(dateLabel, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.grey)),
                            Text(formattedDate, style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: hasPayroll ? Colors.green.shade700 : Colors.black87)),
                          ],
                        ),
                        if (!isDesigner)
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              const Text('THÙ LAO', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.grey)),
                              Text(
                                  '+${NumberFormat.decimalPattern('vi_VN').format(reward)} đ',
                                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: hasPayroll ? Colors.green.shade600 : Colors.orange.shade600)
                              ),
                            ],
                          ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}