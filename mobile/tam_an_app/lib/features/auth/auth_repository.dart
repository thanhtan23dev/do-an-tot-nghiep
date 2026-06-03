import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../core/api/api_client.dart';

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository();
});

class AuthRepository {
  final Dio _dio = ApiClient().dio;
  Future<void> login(String phone, String password, String? fcmToken) async {
    try {
      final Map<String, dynamic> requestData = {
        'phone': phone,
        'password': password,
      };
      if (fcmToken != null && fcmToken.isNotEmpty) {
        requestData['fcm_token'] = fcmToken;
      }
      final response = await _dio.post('/login', data: requestData);
      if (response.statusCode == 200 && response.data['success'] == true) {
        final String token = response.data['data']['token'];
        final int role = int.parse(response.data['data']['user']['role'].toString());
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', token);
        await prefs.setInt('user_role', role);
      } else {
        throw Exception('Đăng nhập thất bại do server từ chối.');
      }
    } on DioException catch (e) {
      final errorMessage = e.response?.data['message'] ?? 'Lỗi kết nối máy chủ. Vui lòng thử lại!';
      throw Exception(errorMessage);
    } catch (e) {
      throw Exception('Có lỗi xảy ra: ${e.toString()}');
    }
  }


  Future<void> logout() async {
    try {
      await _dio.post('/logout');
    } catch (e) {
      print('Lỗi gọi API logout (có thể do mất mạng): $e');
    } finally {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('auth_token');
      await prefs.remove('user_role');
    }
  }
  Future<void> register(String name, String phone, String password) async {
    final response = await _dio.post('/register', data: {
      'name': name,
      'phone': phone,
      'password': password,
    });
    if (response.statusCode != 200 || response.data['success'] != true) {
      throw Exception(response.data['message'] ?? 'Đăng ký thất bại');
    }
  }
  Future<void> updateProfile(String name, String? password) async {
    final data = {'name': name};
    if (password != null && password.isNotEmpty) {
      data['password'] = password;
    }
    final response = await _dio.put('/profile/update', data: data);
    if (response.statusCode != 200 || response.data['success'] != true) {
      throw Exception(response.data['message'] ?? 'Cập nhật thất bại');
    }
  }
  Future<void> deleteAccount() async {
    final response = await _dio.delete('/account/delete');
    if (response.statusCode != 200 || response.data['success'] != true) {
      throw Exception(response.data['message'] ?? 'Không thể xóa tài khoản');
    }
  }
}