import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'jobs_repository.dart';

final completedJobsSearchProvider = StateProvider<String>((ref) => '');

final completedJobsProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final search = ref.watch(completedJobsSearchProvider);
  final repository = ref.read(jobsRepositoryProvider);
  return await repository.fetchCompletedJobs(search);
});