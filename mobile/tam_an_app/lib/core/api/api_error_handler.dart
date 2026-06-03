import 'package:dio/dio.dart';
import '../exceptions/api_exception.dart';

extension DioErrorHandler on Future<Response> {

  Future<Response> catchApiError() async {
    try {
      return await this;

    } on DioException catch (e) {
      if (e.response != null && e.response?.data != null) {
        final errorMessage = e.response?.data['message'] ?? 'Lỗi không xác định từ máy chủ';
        throw ApiException(errorMessage);
      }
      throw ApiException('Lỗi kết nối: ${e.message}');

    } catch (e) {
      throw ApiException('Đã xảy ra lỗi: $e');
    }
  }
}