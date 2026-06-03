import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/api/api_client.dart';
import '../../core/api/api_error_handler.dart';

final salaryAdvanceRepositoryProvider = Provider<SalaryAdvanceRepository>((ref) {
  return SalaryAdvanceRepository();
});

class SalaryAdvanceRepository {
  final _dio = ApiClient().dio;
  
  Future<List<dynamic>> fetchAdvances() async {
    final response = await _dio.get('/salary-advance').catchApiError();
    if (response.statusCode == 200 && response.data['success'] == true) {
      return response.data['data']['data'] ?? [];
    }
    throw Exception('Không thể tải lịch sử phiếu lương');
  }

  // 2. Lấy thông tin số dư (để vẽ màn hình Tạo yêu cầu)
  Future<Map<String, dynamic>> fetchAdvanceInfo() async {
    final response = await _dio.get('/salary-advance/info').catchApiError();
    if (response.statusCode == 200 && response.data['success'] == true) {
      return response.data['data'];
    }
    throw Exception('Không thể tải thông tin số dư');
  }

  // 3. Gửi yêu cầu ứng tiền
  Future<void> submitAdvance(double amount) async {
    final response = await _dio.post('/salary-advance', data: {
      'withdrawn_amount': amount,
    }).catchApiError();

    if (response.statusCode != 200 || response.data['success'] != true) {
      throw Exception(response.data['message'] ?? 'Gửi yêu cầu thất bại');
    }
  }

  // 4. Lấy chi tiết 1 phiếu lương
  Future<Map<String, dynamic>> fetchAdvanceDetail(int id) async {
    final response = await _dio.get('/salary-advance/$id').catchApiError();
    if (response.statusCode == 200 && response.data['success'] == true) {
      return response.data['data'];
    }
    throw Exception('Không thể tải chi tiết phiếu lương');
  }
}