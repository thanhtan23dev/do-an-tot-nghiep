import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'jobs_repository.dart';

typedef JobDetailArgs = ({String type, int id});

final jobDetailProvider = FutureProvider.autoDispose.family<Map<String, dynamic>, JobDetailArgs>((ref, args) async {
  final repository = ref.read(jobsRepositoryProvider);
  return await repository.fetchJobDetail(args.type, args.id);
});