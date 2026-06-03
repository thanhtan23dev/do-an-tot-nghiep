import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'main.dart';
import 'features/main_layout.dart';

class NotificationService {
  final FirebaseMessaging _firebaseMessaging = FirebaseMessaging.instance;

  Future<String?> initialize() async {
    NotificationSettings settings = await _firebaseMessaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    if (settings.authorizationStatus == AuthorizationStatus.authorized) {
      print('✅ Người dùng đã cấp quyền nhận thông báo!');
    } else {
      print('❌ Người dùng TỪ CHỐI cấp quyền thông báo.');
    }

    String? token = await _firebaseMessaging.getToken();
    print('🔥 BÍ MẬT - FCM TOKEN CỦA MÁY NÀY LÀ: $token');

    RemoteMessage? initialMessage = await _firebaseMessaging.getInitialMessage();
    if (initialMessage != null) {
      Future.delayed(const Duration(seconds: 1), () {
        _handleNotificationClick(initialMessage);
      });
    }

    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      _handleNotificationClick(message);
    });

    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      print('📩 Nhận được thông báo khi đang mở app: ${message.notification?.title}');
      if (message.notification != null) {
        final context = scaffoldMessengerKey.currentContext;
        double statusBarHeight = context != null
            ? MediaQuery.of(context).padding.top
            : 30;
        double pushUpMargin = context != null
            ? MediaQuery.of(context).size.height - statusBarHeight - 200
            : 600;

        scaffoldMessengerKey.currentState?.showSnackBar(
          SnackBar(
            content: Container(
              padding: const EdgeInsets.symmetric(vertical: 4),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Icon(Icons.notifications_active_outlined, color: Colors.blue.shade100, size: 30),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          message.notification!.title ?? 'Thông báo mới',
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.white),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          message.notification!.body ?? '',
                          style: const TextStyle(fontSize: 14, color: Colors.white70),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            behavior: SnackBarBehavior.floating,
            backgroundColor: Colors.blue.shade800,
            duration: const Duration(seconds: 4),
            margin: EdgeInsets.only(
              bottom: pushUpMargin,
              left: 16,
              right: 16,
            ),
            elevation: 10,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(20),
            ),
            dismissDirection: DismissDirection.up,

            action: SnackBarAction(
              label: 'XEM',
              textColor: Colors.amberAccent,
              onPressed: () {
                _handleNotificationClick(message);
              },
            ),
          ),
        );
      }
    });

    return token;
  }

  void _handleNotificationClick(RemoteMessage message) {
    print("👉 Người dùng vừa CLICK vào thông báo: ${message.notification?.title}");

    int targetTabIndex = 0;

    if (message.data.containsKey('type')) {
      String type = message.data['type'];
      print("📦 Dữ liệu ngầm nhận được: Loại = $type");


      switch (type) {
        case 'new_order':
        // case 'rejected_order':
        case 'new_task':
        case 'task_rejected':
          targetTabIndex = 1;
          break;

        case 'salary_advance':
          targetTabIndex = 2;
          break;

        default:
          targetTabIndex = 0;
      }
    }

    navigatorKey.currentState?.pushAndRemoveUntil(
      MaterialPageRoute(
        builder: (context) => MainLayout(initialIndex: targetTabIndex),
      ),
          (route) => false,
    );
  }
}