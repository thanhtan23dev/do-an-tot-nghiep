import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'jobs_repository.dart';

final activeJobsSearchProvider = StateProvider<String>((ref) => '');

final activeJobsProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final search = ref.watch(activeJobsSearchProvider);
  final repository = ref.read(jobsRepositoryProvider);
  return await repository.fetchActiveJobs(search);
});