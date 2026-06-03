import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'salary_advance_repository.dart';

// Provider lấy danh sách Lịch sử
final salaryAdvancesProvider = FutureProvider.autoDispose<List<dynamic>>((ref) async {
  final repository = ref.read(salaryAdvanceRepositoryProvider);
  return await repository.fetchAdvances();
});

// Provider lấy thông số Két sắt (Số dư)
final salaryAdvanceInfoProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final repository = ref.read(salaryAdvanceRepositoryProvider);
  return await repository.fetchAdvanceInfo();
});

// Provider lấy chi tiết 1 Phiếu lương
final salaryAdvanceDetailProvider = FutureProvider.autoDispose.family<Map<String, dynamic>, int>((ref, id) async {
  final repository = ref.read(salaryAdvanceRepositoryProvider);
  return await repository.fetchAdvanceDetail(id);
});