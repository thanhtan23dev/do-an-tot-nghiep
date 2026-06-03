import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'new_jobs_provider.dart';
import 'jobs_repository.dart';
import 'job_detail_screen.dart';

class NewJobsScreen extends ConsumerStatefulWidget {
  const NewJobsScreen({super.key});

  @override
  ConsumerState<NewJobsScreen> createState() => _NewJobsScreenState();
}

class _NewJobsScreenState extends ConsumerState<NewJobsScreen> {
  final TextEditingController _searchController = TextEditingController();

  // Hàm xử lý Nhận/Từ chối việc
  Future<void> _handleJobAction(String type, int id, String action) async {
    final repo = ref.read(jobsRepositoryProvider);
    try {
      if (action == 'accept') {
        await repo.acceptJob(type, id);
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Đã nhận việc thành công!'), backgroundColor: Colors.green)
        );
      } else {
        await repo.rejectJob(type, id);
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Đã từ chối việc!'), backgroundColor: Colors.red)
        );
      }
      ref.refresh(newJobsProvider);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Có lỗi xảy ra, vui lòng thử lại!'), backgroundColor: Colors.red)
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final jobsState = ref.watch(newJobsProvider);
    final searchQuery = ref.watch(newJobsSearchProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // THANH TÌM KIẾM
              TextField(
                controller: _searchController,
                onSubmitted: (value) => ref.read(newJobsSearchProvider.notifier).state = value,
                decoration: InputDecoration(
                  hintText: 'Tìm tên công việc hoặc mã...',
                  prefixIcon: const Icon(Icons.search, color: Colors.grey),
                  suffixIcon: searchQuery.isNotEmpty
                      ? IconButton(
                    icon: const Icon(Icons.close, color: Colors.redAccent),
                    onPressed: () {
                      _searchController.clear();
                      ref.read(newJobsSearchProvider.notifier).state = '';
                    },
                  )
                      : null,
                  filled: true,
                  fillColor: Colors.white,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide.none,
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // DANH SÁCH CÔNG VIỆC
              Expanded(
                child: jobsState.when(
                  loading: () => const Center(child: CircularProgressIndicator()),
                  error: (err, stack) => Center(
                    child: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Text(
                        // Nếu là Thiết kế đăng nhập, backend sẽ trả về lỗi. Hiển thị lỗi đó ra.
                        err.toString().replaceAll('Exception: ', ''),
                        style: const TextStyle(color: Colors.red, fontSize: 16, fontWeight: FontWeight.w500),
                        textAlign: TextAlign.center,
                      ),
                    ),
                  ),
                  data: (data) {
                    final List items = data['items'] ?? data['data']?['data'] ?? []; // Đảm bảo parse đúng JSON Pagination của Laravel
                    final String type = data['type'] ?? 'task';

                    // TRƯỜNG HỢP TRỐNG
                    if (items.isEmpty) {
                      return RefreshIndicator(
                        onRefresh: () async => ref.refresh(newJobsProvider),
                        child: ListView(
                          physics: const AlwaysScrollableScrollPhysics(),
                          children: [
                            SizedBox(
                              height: MediaQuery.of(context).size.height * 0.6,
                              child: Center(
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Icon(Icons.assignment_turned_in_outlined, size: 64, color: Colors.blue.shade200),
                                    const SizedBox(height: 16),
                                    Text(
                                      searchQuery.isNotEmpty ? 'Không tìm thấy kết quả' : 'Chưa có công việc mới',
                                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                                    ),
                                    const SizedBox(height: 8),
                                    Text(
                                      searchQuery.isNotEmpty
                                          ? 'Không khớp với từ khóa "$searchQuery".'
                                          : 'Hiện tại quản lý chưa giao thêm việc nào cho bạn.\nVuốt xuống để tải lại.',
                                      textAlign: TextAlign.center,
                                      style: const TextStyle(color: Colors.grey),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ],
                        ),
                      );
                    }

                    // TRƯỜNG HỢP CÓ DỮ LIỆU
                    return RefreshIndicator(
                      onRefresh: () async => ref.refresh(newJobsProvider),
                      child: ListView.separated(
                        physics: const AlwaysScrollableScrollPhysics(),
                        itemCount: items.length,
                        separatorBuilder: (context, index) => const SizedBox(height: 16),
                        itemBuilder: (context, index) {
                          final item = items[index];

                          // Đã bỏ isOrder. Giao diện giờ chỉ thuần cho Task thi công
                          final String orderName = item['order']?['name'] ?? 'Không rõ';
                          final String code = 'Đơn: $orderName';

                          final double reward = double.tryParse(item['reward'].toString()) ?? 0;
                          final formattedReward = NumberFormat.decimalPattern('vi_VN').format(reward);
                          final deadline = item['deadline'] != null
                              ? DateFormat('HH:mm dd/MM/yyyy').format(DateTime.parse(item['deadline']).toLocal())
                              : 'Chưa có';

                          return InkWell(
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => JobDetailScreen(type: type, id: item['id']),
                                ),
                              ).then((_) => ref.refresh(newJobsProvider)); // Refresh khi quay lại
                            },
                            borderRadius: BorderRadius.circular(16),
                            child: Container(
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: Colors.grey.shade200),
                              ),
                              child: Row(
                                children: [
                                  // Viền màu xanh cố định cho Task
                                  Container(
                                    width: 6,
                                    height: 160,
                                    decoration: BoxDecoration(
                                      color: Colors.blue.shade500,
                                      borderRadius: const BorderRadius.only(
                                          topLeft: Radius.circular(16),
                                          bottomLeft: Radius.circular(16)
                                      ),
                                    ),
                                  ),
                                  Expanded(
                                    child: Padding(
                                      padding: const EdgeInsets.all(16.0),
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Row(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Expanded(
                                                child: Column(
                                                  crossAxisAlignment: CrossAxisAlignment.start,
                                                  children: [
                                                    Text(code, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.grey)),
                                                    const SizedBox(height: 4),
                                                    Text(
                                                        item['name'],
                                                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                                                        maxLines: 2,
                                                        overflow: TextOverflow.ellipsis
                                                    ),
                                                  ],
                                                ),
                                              ),
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                                decoration: BoxDecoration(
                                                    color: Colors.yellow.shade100,
                                                    borderRadius: BorderRadius.circular(12)
                                                ),
                                                child: Text('Việc mới', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.yellow.shade800)),
                                              )
                                            ],
                                          ),
                                          if (item['description'] != null && item['description'].toString().trim().isNotEmpty) ...[
                                            const SizedBox(height: 8),
                                            Container(
                                              padding: const EdgeInsets.all(8),
                                              decoration: BoxDecoration(
                                                  color: Colors.grey.shade50,
                                                  borderRadius: BorderRadius.circular(8)
                                              ),
                                              child: Text(
                                                  item['description'],
                                                  style: TextStyle(fontSize: 13, color: Colors.grey.shade700),
                                                  maxLines: 2,
                                                  overflow: TextOverflow.ellipsis
                                              ),
                                            ),
                                          ],
                                          const SizedBox(height: 12),
                                          Row(
                                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                            children: [
                                              Row(
                                                children: [
                                                  const Icon(Icons.access_time, size: 14, color: Colors.grey),
                                                  const SizedBox(width: 4),
                                                  Text('Hạn: $deadline', style: const TextStyle(fontSize: 12, color: Colors.grey, fontWeight: FontWeight.w500)),
                                                ],
                                              ),
                                              Text(
                                                  '+$formattedReward đ',
                                                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: Colors.blue.shade600)
                                              ),
                                            ],
                                          ),
                                          const Divider(height: 24),
                                          Row(
                                            children: [
                                              Expanded(
                                                child: OutlinedButton(
                                                  onPressed: () => _handleJobAction(type, item['id'], 'reject'),
                                                  style: OutlinedButton.styleFrom(
                                                    foregroundColor: Colors.red,
                                                    side: const BorderSide(color: Colors.redAccent),
                                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                                  ),
                                                  child: const Text('Từ chối'),
                                                ),
                                              ),
                                              const SizedBox(width: 12),
                                              Expanded(
                                                child: ElevatedButton(
                                                  onPressed: () => _handleJobAction(type, item['id'], 'accept'),
                                                  style: ElevatedButton.styleFrom(
                                                    backgroundColor: Colors.green,
                                                    foregroundColor: Colors.white,
                                                    elevation: 0,
                                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                                  ),
                                                  child: const Text('Nhận việc'),
                                                ),
                                              ),
                                            ],
                                          )
                                        ],
                                      ),
                                    ),
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
              ),
            ],
          ),
        ),
      ),
    );
  }
}