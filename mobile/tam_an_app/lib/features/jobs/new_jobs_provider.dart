import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'jobs_repository.dart';

// Provider lưu từ khóa tìm kiếm (mặc định là rỗng)
final newJobsSearchProvider = StateProvider<String>((ref) => '');

// Provider tải danh sách (sẽ tự động gọi lại API nếu biến search thay đổi)
final newJobsProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final search = ref.watch(newJobsSearchProvider);
  final repository = ref.read(jobsRepositoryProvider);
  return await repository.fetchNewJobs(search);
});