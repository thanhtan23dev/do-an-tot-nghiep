import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'dashboard_repository.dart';

final dashboardProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final repository = ref.read(dashboardRepositoryProvider);
  return await repository.fetchDashboardData();
});