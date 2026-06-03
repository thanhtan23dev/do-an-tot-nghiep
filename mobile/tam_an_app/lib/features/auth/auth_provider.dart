import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'auth_repository.dart';

final authControllerProvider = StateNotifierProvider<AuthController, AsyncValue<void>>((ref) {
  final repository = ref.read(authRepositoryProvider);
  return AuthController(repository);
});

class AuthController extends StateNotifier<AsyncValue<void>> {
  final AuthRepository _repository;

  AuthController(this._repository) : super(const AsyncData(null));

  Future<bool> login(String phone, String password) async {
    state = const AsyncLoading();
    try {

      String? freshFcmToken;
      try {
        freshFcmToken = await FirebaseMessaging.instance.getToken();
        print("FCM Token lấy được: $freshFcmToken");
      } catch (e) {
        print("Không thể lấy FCM Token: $e");
      }

      await _repository.login(phone, password, freshFcmToken);

      state = const AsyncData(null);
      return true;
    } catch (e) {
      final cleanError = e.toString().replaceAll('Exception: ', '');
      state = AsyncError(cleanError, StackTrace.current);
      return false;
    }
  }

  Future<void> logout() async {
    state = const AsyncLoading();
    await _repository.logout();
    state = const AsyncData(null);
  }

  Future<bool> register(String name, String phone, String password) async {
    state = const AsyncLoading();
    try {
      await _repository.register(name, phone, password);
      state = const AsyncData(null);
      return true;
    } catch (e) {
      final cleanError = e.toString().replaceAll('Exception: ', '');
      state = AsyncError(cleanError, StackTrace.current);
      return false;
    }
  }

  Future<bool> updateProfile(String name, String? password) async {
    state = const AsyncLoading();
    try {
      await _repository.updateProfile(name, password);
      state = const AsyncData(null);
      return true;
    } catch (e) {
      final cleanError = e.toString().replaceAll('Exception: ', '');
      state = AsyncError(cleanError, StackTrace.current);
      return false;
    }
  }

  Future<bool> deleteAccount() async {
    state = const AsyncLoading();
    try {
      await _repository.deleteAccount();
      await _repository.logout();
      state = const AsyncData(null);
      return true;
    } catch (e) {
      final cleanError = e.toString().replaceAll('Exception: ', '');
      state = AsyncError(cleanError, StackTrace.current);
      return false;
    }
  }
}