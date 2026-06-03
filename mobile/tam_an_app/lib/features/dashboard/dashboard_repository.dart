import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/api/api_client.dart';

final dashboardRepositoryProvider = Provider<DashboardRepository>((ref) {
  return DashboardRepository();
});

class DashboardRepository {
  final _dio = ApiClient().dio;

  Future<Map<String, dynamic>> fetchDashboardData() async {
    try {
      final response = await _dio.get('/dashboard');
      if (response.statusCode == 200 && response.data['success'] == true) {
        return response.data['data'];
      }
      throw Exception('Không thể tải dữ liệu trang chủ');
    } catch (e) {
      throw Exception('Lỗi kết nối hoặc máy chủ từ chối: $e');
    }
  }
}