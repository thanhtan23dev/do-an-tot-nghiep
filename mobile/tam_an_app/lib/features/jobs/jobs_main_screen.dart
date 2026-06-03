import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'active_jobs_screen.dart';
import 'completed_jobs_screen.dart';
import 'new_jobs_screen.dart';

class JobsMainScreen extends ConsumerStatefulWidget {
  const JobsMainScreen({super.key});

  @override
  ConsumerState<JobsMainScreen> createState() => _JobsMainScreenState();
}

class _JobsMainScreenState extends ConsumerState<JobsMainScreen> with SingleTickerProviderStateMixin {
  TabController? _tabController;
  bool _isDesigner = false;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _initRoleAndTabs();
  }

  // Hàm lấy Role từ SharedPreferences giống hệt với lúc bạn lưu ở AuthRepository
  Future<void> _initRoleAndTabs() async {
    try {
      final prefs = await SharedPreferences.getInstance();

      // Lấy user_role (nếu không có thì mặc định là 2 - Thợ)
      final int role = prefs.getInt('user_role') ?? 2;

      _isDesigner = (role == 1);
    } catch (e) {
      _isDesigner = false;
    }

    // Khởi tạo TabController: Thiết kế có 2 tab, Thợ có 3 tab
    _tabController = TabController(length: _isDesigner ? 2 : 3, vsync: this);

    if (mounted) {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  void dispose() {
    _tabController?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    // Hiển thị loading trong tích tắc lúc đang đọc bộ nhớ máy
    if (_isLoading || _tabController == null) {
      return const Center(child: CircularProgressIndicator());
    }

    return Column(
      children: [
        // --- 1. KHU VỰC TABBAR ---
        Container(
          margin: const EdgeInsets.fromLTRB(16, 16, 16, 8),
          padding: const EdgeInsets.all(4),
          decoration: BoxDecoration(
            color: Colors.grey.shade200,
            borderRadius: BorderRadius.circular(16),
          ),
          child: TabBar(
            controller: _tabController!,
            dividerColor: Colors.transparent,
            indicatorSize: TabBarIndicatorSize.tab,
            indicator: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.04),
                  blurRadius: 4,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            labelColor: Colors.blue.shade700,
            labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
            unselectedLabelColor: Colors.grey.shade600,

            // Build các Tabs dựa trên role
            tabs: [
              if (!_isDesigner) const Tab(text: 'Mới giao'),
              const Tab(text: 'Đang làm'),
              const Tab(text: 'Lịch sử'),
            ],
          ),
        ),

        // --- 2. KHU VỰC HIỂN THỊ MÀN HÌNH CON ---
        Expanded(
          child: TabBarView(
            controller: _tabController!,
            children: [
              if (!_isDesigner) const NewJobsScreen(),
              const ActiveJobsScreen(),
              const CompletedJobsScreen(),
            ],
          ),
        ),
      ],
    );
  }
}