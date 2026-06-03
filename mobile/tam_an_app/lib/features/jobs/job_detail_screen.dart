import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import 'job_detail_provider.dart';

class JobDetailScreen extends ConsumerWidget {
  final String type;
  final int id;
  final bool isDesigner;

  const JobDetailScreen({
    super.key,
    required this.type,
    required this.id,
    this.isDesigner = false,
  });

  // Hàm tạo Full URL
  String _getFullUrl(String path) {
    if (path.startsWith('http')) return path;
    return 'https://thainguyenonline.com/storage/$path';
  }

  // Hàm mở URL trên trình duyệt / thiết bị
  Future<void> _launchURL(BuildContext context, String urlString) async {
    final Uri url = Uri.parse(urlString);
    try {
      if (!await launchUrl(url, mode: LaunchMode.externalApplication)) {
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Không thể mở liên kết này!'), backgroundColor: Colors.red));
        }
      }
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Lỗi mở file: $e'), backgroundColor: Colors.red));
      }
    }
  }

  // Hàm Parse dữ liệu JSON mảng File từ API
  List<Map<String, dynamic>> _parseFiles(dynamic data) {
    if (data == null || data == 'null' || data == '' || data == '[]') return [];
    try {
      if (data is String) {
        final parsed = jsonDecode(data);
        if (parsed is List) return List<Map<String, dynamic>>.from(parsed);
      } else if (data is List) {
        return List<Map<String, dynamic>>.from(data);
      }
    } catch (e) {
      return [{'download_link': data.toString(), 'original_name': 'Tai_lieu_dinh_kem'}];
    }
    return [];
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final jobState = ref.watch(jobDetailProvider((type: type, id: id)));

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Chi tiết công việc', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 0,
        centerTitle: true,
      ),
      body: jobState.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, stack) => Center(
            child: Text(
              err.toString().replaceAll('Exception: ', ''),
              style: const TextStyle(color: Colors.red, fontSize: 16),
              textAlign: TextAlign.center,
            )
        ),
        data: (item) {
          final isOrder = type == 'order';

          String badgeText = 'Thi công';
          if (isDesigner) {
            badgeText = isOrder ? 'Thiết kế tổng thể' : 'Thiết kế chi tiết';
          }

          final double reward = double.tryParse(item['reward']?.toString() ?? '0') ?? 0;
          final formattedReward = NumberFormat.decimalPattern('vi_VN').format(reward);
          final String deadlineStr = item['deadline'] != null
              ? DateFormat('HH:mm dd/MM/yyyy').format(DateTime.parse(item['deadline']).toLocal())
              : 'Chưa có hạn';

          final designFiles = _parseFiles(isOrder ? item['design_image'] : item['order']?['design_image']);
          final quyCachFiles = _parseFiles(isOrder ? item['quy_cach'] : item['order']?['quy_cach']);
          final taskFiles = _parseFiles(item['task_files']);

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // 1. KHỐI THÔNG TIN CHUNG
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(24), border: Border.all(color: Colors.grey.shade200), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 10, offset: const Offset(0, 4))]),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isOrder ? 'Mã Đơn: #${item['id']}' : 'Đơn: ${item['order']?['name'] ?? 'N/A'}',
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey, letterSpacing: 1),
                      ),
                      const SizedBox(height: 8),
                      Text(item['name'], style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900, color: const Color(0xFF1F2937))),
                      const SizedBox(height: 16),
                      Wrap(
                        spacing: 8, runSpacing: 8,
                        children: [
                          Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              decoration: BoxDecoration(color: Colors.blue.shade50, borderRadius: BorderRadius.circular(8)),
                              child: Text(badgeText, style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.blue.shade700))
                          ),
                          Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              decoration: BoxDecoration(color: Colors.grey.shade100, borderRadius: BorderRadius.circular(8)),
                              child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Icon(Icons.access_time, size: 14, color: Colors.grey),
                                    const SizedBox(width: 4),
                                    Text('Hạn: $deadlineStr', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey))
                                  ]
                              )
                          ),
                        ],
                      ),
                      if (item['description'] != null && item['description'].toString().isNotEmpty) ...[
                        const Padding(padding: EdgeInsets.symmetric(vertical: 16), child: Divider(height: 1)),
                        const Text('MÔ TẢ CÔNG VIỆC', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.grey, letterSpacing: 1)),
                        const SizedBox(height: 8),
                        Text(item['description'], style: const TextStyle(fontSize: 14, color: Color(0xFF4B5563), height: 1.5)),
                      ]
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // 2. KHỐI THÔNG SỐ VÀ TIẾN ĐỘ (Chỉ dành cho Task)
                if (!isOrder) ...[
                  const Padding(
                    padding: EdgeInsets.only(left: 8, bottom: 8),
                    child: Text('THÔNG SỐ & TIẾN ĐỘ', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey, letterSpacing: 1)),
                  ),
                  GridView.count(
                    crossAxisCount: 2,
                    crossAxisSpacing: 12,
                    mainAxisSpacing: 12,
                    childAspectRatio: 2.2,
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    children: [
                      _buildSpecBox('Kích thước (D-R-C)', '${item['length'] ?? 0} x ${item['width'] ?? 0} x ${item['height'] ?? 0}', Colors.grey),
                      _buildSpecBox('Số lượng', '${item['item_count'] ?? 1}', Colors.blue),
                      _buildSpecBox('Đã hoàn thành', '${item['completed_count'] ?? 0} / ${item['item_count'] ?? 1}', Colors.green),
                      _buildSpecBox('Tổng khối lượng', '${item['quantity'] ?? 0} ${item['unit'] ?? 'M3'}', Colors.purple),
                    ],
                  ),
                  const SizedBox(height: 16),
                ],

                // 3. LÝ DO TỪ CHỐI
                if (item['reject_reason'] != null)
                  Container(
                    width: double.infinity,
                    margin: const EdgeInsets.only(bottom: 16),
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.red.shade200)),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Icon(Icons.warning_amber_rounded, color: Colors.red.shade600, size: 18),
                            const SizedBox(width: 8),
                            Text('SẾP CHÚ THÍCH SỬA LẠI', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.red.shade800, letterSpacing: 1)),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(item['reject_reason'], style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: Colors.red.shade700)),
                      ],
                    ),
                  ),

                // 4. KHỐI THÙ LAO
                if (!isDesigner && !isOrder)
                  Container(
                    width: double.infinity,
                    margin: const EdgeInsets.only(bottom: 16),
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(color: Colors.blue.shade50, borderRadius: BorderRadius.circular(24), border: Border.all(color: Colors.blue.shade100)),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Thù lao thi công dự kiến:', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.blue.shade600)),
                        const SizedBox(height: 4),
                        Text('$formattedReward VNĐ', style: TextStyle(fontSize: 28, fontWeight: FontWeight.w900, color: Colors.blue.shade700)),
                      ],
                    ),
                  ),

                // 5. KHỐI TÀI LIỆU KỸ THUẬT
                if (taskFiles.isNotEmpty || designFiles.isNotEmpty || quyCachFiles.isNotEmpty) ...[
                  const Padding(
                    padding: EdgeInsets.only(left: 8, bottom: 12, top: 8),
                    child: Text('TÀI LIỆU KỸ THUẬT', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey, letterSpacing: 1)),
                  ),

                  if (taskFiles.isNotEmpty) ...[
                    Padding(
                      padding: const EdgeInsets.only(bottom: 8, left: 4),
                      child: Text('Bản vẽ chi tiết công việc:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey.shade600)),
                    ),
                    ...taskFiles.map((file) => _buildFileCard(context, file, Colors.orange, Icons.attach_file)),
                    const SizedBox(height: 8),
                  ],

                  if (designFiles.isNotEmpty || quyCachFiles.isNotEmpty) ...[
                    Padding(
                      padding: const EdgeInsets.only(bottom: 8, left: 4),
                      child: Text('Tài liệu tổng quan đơn hàng:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey.shade600)),
                    ),
                    ...designFiles.map((file) => _buildFileCard(context, file, Colors.blue, Icons.image)),
                    ...quyCachFiles.map((file) => _buildFileCard(context, file, Colors.red, Icons.picture_as_pdf)),
                  ]
                ] else ...[
                  Container(
                    width: double.infinity,
                    height: 120,
                    decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(20), border: Border.all(color: Colors.grey.shade300, style: BorderStyle.solid)),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.folder_off_outlined, color: Colors.grey.shade400, size: 32),
                        const SizedBox(height: 8),
                        Text('Chưa có tài liệu kỹ thuật nào', style: TextStyle(color: Colors.grey.shade500, fontWeight: FontWeight.w500)),
                      ],
                    ),
                  ),
                ]
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildSpecBox(String title, String value, MaterialColor color) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.shade50.withOpacity(0.5),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.shade100),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(title, style: TextStyle(fontSize: 10, color: color.shade600, fontWeight: FontWeight.w600), maxLines: 1, overflow: TextOverflow.ellipsis),
          const SizedBox(height: 4),
          Text(value, style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: color.shade800), maxLines: 1, overflow: TextOverflow.ellipsis),
        ],
      ),
    );
  }

  Widget _buildFileCard(BuildContext context, Map<String, dynamic> file, MaterialColor color, IconData icon) {
    return InkWell(
      onTap: () {
        final url = _getFullUrl(file['download_link'] ?? '');
        _launchURL(context, url); // Kích hoạt mở file
      },
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(20), border: Border.all(color: color.shade100)),
        child: Row(
          children: [
            Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(color: color.shade50, borderRadius: BorderRadius.circular(12)),
                child: Icon(icon, color: color.shade500)
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(file['original_name'] ?? 'Tài liệu', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14), maxLines: 1, overflow: TextOverflow.ellipsis),
                  const SizedBox(height: 4),
                  const Text('Bấm để xem/tải về', style: TextStyle(fontSize: 11, color: Colors.grey)),
                ],
              ),
            ),
            Icon(Icons.open_in_new_rounded, size: 16, color: color.shade300),
          ],
        ),
      ),
    );
  }
}