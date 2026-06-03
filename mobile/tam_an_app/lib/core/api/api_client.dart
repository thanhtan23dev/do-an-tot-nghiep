import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/app_constants.dart';

class ApiClient {
  static final ApiClient _instance = ApiClient._internal();
  late Dio dio;

  factory ApiClient() {
    return _instance;
  }

  ApiClient._internal() {
    dio = Dio(BaseOptions(
      baseUrl: AppConstants.baseUrl,
      connectTimeout: const Duration(seconds: 15), // Chờ kết nối 15s
      receiveTimeout: const Duration(seconds: 15), // Chờ nhận dữ liệu 15s
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ));

    // THÊM INTERCEPTOR: Kẻ gác cổng tự động
    dio.interceptors.add(InterceptorsWrapper(

      // 1. TRƯỚC KHI GỬI REQUEST: Tự động móc Token từ bộ nhớ nhét vào Header
      onRequest: (options, handler) async {
        final prefs = await SharedPreferences.getInstance();
        final token = prefs.getString('auth_token'); // Tên key chúng ta sẽ lưu khi Login

        if (token != null && token.isNotEmpty) {
          options.headers['Authorization'] = 'Bearer $token';
        }

        print('🌐 [API GỬI ĐI] ${options.method} ${options.uri}'); // Log ra Terminal để dễ debug
        return handler.next(options); // Cho phép Request đi tiếp
      },

      // 2. KHI NHẬN RESPONSE VỀ: Log ra để xem dữ liệu
      onResponse: (response, handler) {
        print('✅ [API THÀNH CÔNG] ${response.requestOptions.path}');
        return handler.next(response);
      },

      // 3. KHI CÓ LỖI XẢY RA
      onError: (DioException e, handler) {
        print('❌ [API LỖI] ${e.requestOptions.path} - ${e.response?.statusCode}');

        // Nếu lỗi 401 (Unauthorized) nghĩa là Token hết hạn hoặc bị đuổi
        if (e.response?.statusCode == 401) {
          // TODO: Xóa Token và đá người dùng về trang Đăng nhập (Sẽ làm ở bước sau)
        }

        return handler.next(e);
      },
    ));
  }
}