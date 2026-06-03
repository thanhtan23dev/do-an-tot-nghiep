import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:image_picker/image_picker.dart';
import 'package:file_picker/file_picker.dart';
import 'active_jobs_provider.dart';
import 'jobs_repository.dart';
import 'job_detail_screen.dart';

class ActiveJobsScreen extends ConsumerStatefulWidget {
  const ActiveJobsScreen({super.key});

  @override
  ConsumerState<ActiveJobsScreen> createState() => _ActiveJobsScreenState();
}

class _ActiveJobsScreenState extends ConsumerState<ActiveJobsScreen> {
  final TextEditingController _searchController = TextEditingController();

  @override
  Widget build(BuildContext context) {
    final jobsState = ref.watch(activeJobsProvider);
    final searchQuery = ref.watch(activeJobsSearchProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              TextField(
                controller: _searchController,
                onSubmitted: (value) => ref.read(activeJobsSearchProvider.notifier).state = value,
                decoration: InputDecoration(
                  hintText: 'Tìm tên công việc hoặc mã...',
                  prefixIcon: const Icon(Icons.search, color: Colors.grey),
                  suffixIcon: searchQuery.isNotEmpty
                      ? IconButton(
                    icon: const Icon(Icons.close, color: Colors.redAccent),
                    onPressed: () {
                      _searchController.clear();
                      ref.read(activeJobsSearchProvider.notifier).state = '';
                    },
                  )
                      : null,
                  filled: true,
                  fillColor: Colors.white,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                ),
              ),
              const SizedBox(height: 16),

              Expanded(
                child: jobsState.when(
                  loading: () => const Center(child: CircularProgressIndicator()),
                  error: (err, stack) => Center(
                    child: Text(
                      err.toString().replaceAll('Exception: ', ''),
                      style: const TextStyle(color: Colors.red, fontSize: 16),
                      textAlign: TextAlign.center,
                    ),
                  ),
                  data: (data) {
                    final bool isDesigner = data['is_designer'] ?? false;

                    if (isDesigner) {
                      final List orders = data['data']['orders']['data'] ?? [];
                      final List tasks = data['data']['tasks']['data'] ?? [];

                      if (orders.isEmpty && tasks.isEmpty) {
                        return _buildEmptyState(context, searchQuery);
                      }

                      return RefreshIndicator(
                        onRefresh: () async => ref.refresh(activeJobsProvider),
                        child: ListView(
                          physics: const AlwaysScrollableScrollPhysics(),
                          children: [
                            _buildSectionHeader('Thiết kế Đơn Hàng', Colors.purple),
                            if (orders.isEmpty)
                              _buildEmptySection('Bạn không có yêu cầu Thiết kế Đơn hàng nào đang chờ.')
                            else
                              ...orders.map((item) => Padding(
                                padding: const EdgeInsets.only(bottom: 16),
                                child: ActiveJobCard(item: item, type: 'order', isDesigner: true, ref: ref),
                              )),

                            const SizedBox(height: 16),

                            _buildSectionHeader('Thiết kế Công Việc', Colors.orange),
                            if (tasks.isEmpty)
                              _buildEmptySection('Bạn không có yêu cầu Thiết kế Công việc nào đang chờ.')
                            else
                              ...tasks.map((item) => Padding(
                                padding: const EdgeInsets.only(bottom: 16),
                                child: ActiveJobCard(item: item, type: 'task', isDesigner: true, ref: ref),
                              )),
                          ],
                        ),
                      );
                    } else {
                      final List items = data['data']['data'] ?? [];

                      if (items.isEmpty) {
                        return _buildEmptyState(context, searchQuery);
                      }

                      return RefreshIndicator(
                        onRefresh: () async => ref.refresh(activeJobsProvider),
                        child: ListView.separated(
                          physics: const AlwaysScrollableScrollPhysics(),
                          itemCount: items.length,
                          separatorBuilder: (context, index) => const SizedBox(height: 16),
                          itemBuilder: (context, index) {
                            return ActiveJobCard(item: items[index], type: 'task', isDesigner: false, ref: ref);
                          },
                        ),
                      );
                    }
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title, Color color) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        children: [
          Container(width: 4, height: 20, decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(4))),
          const SizedBox(width: 8),
          Text(title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900, letterSpacing: 0.5)),
        ],
      ),
    );
  }

  Widget _buildEmptySection(String message) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(color: Colors.grey.shade50, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.grey.shade200, style: BorderStyle.solid)),
      child: Center(
        child: Text(message, textAlign: TextAlign.center, style: TextStyle(color: Colors.grey.shade500, fontWeight: FontWeight.w500)),
      ),
    );
  }

  Widget _buildEmptyState(BuildContext context, String searchQuery) {
    return RefreshIndicator(
      onRefresh: () async => ref.refresh(activeJobsProvider),
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          SizedBox(
            height: MediaQuery.of(context).size.height * 0.6,
            child: Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.construction, size: 64, color: Colors.blue.shade200),
                  const SizedBox(height: 16),
                  Text(searchQuery.isNotEmpty ? 'Không tìm thấy kết quả' : 'Không có việc đang làm', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  Text('Vuốt xuống để tải lại', style: TextStyle(fontSize: 13, color: Colors.grey.shade400)),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class ActiveJobCard extends StatefulWidget {
  final Map<String, dynamic> item;
  final String type;
  final bool isDesigner;
  final WidgetRef ref;

  const ActiveJobCard({super.key, required this.item, required this.type, required this.isDesigner, required this.ref});

  @override
  State<ActiveJobCard> createState() => _ActiveJobCardState();
}

class _ActiveJobCardState extends State<ActiveJobCard> {
  File? _selectedImage;
  File? _selectedPdf;
  File? _selectedTaskFile;
  bool _isSubmitting = false;

  Future<void> _pickImage() async {
    final ImagePicker picker = ImagePicker();
    final XFile? image = await picker.pickImage(source: ImageSource.gallery);
    if (image != null) setState(() => _selectedImage = File(image.path));
  }

  Future<void> _pickPdf() async {
    FilePickerResult? result = await FilePicker.platform.pickFiles(type: FileType.custom, allowedExtensions: ['pdf']);
    if (result != null && result.files.single.path != null) {
      setState(() => _selectedPdf = File(result.files.single.path!));
    }
  }

  Future<void> _pickTaskFile() async {
    FilePickerResult? result = await FilePicker.platform.pickFiles();
    if (result != null && result.files.single.path != null) {
      setState(() => _selectedTaskFile = File(result.files.single.path!));
    }
  }

  Future<void> _submitData() async {
    final isOrder = widget.type == 'order';

    if (isOrder && _selectedImage == null && _selectedPdf == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Vui lòng chọn Ảnh Bản vẽ hoặc File Quy cách!'), backgroundColor: Colors.red));
      return;
    }
    if (!isOrder && _selectedTaskFile == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Vui lòng đính kèm file bản vẽ chi tiết!'), backgroundColor: Colors.red));
      return;
    }

    setState(() => _isSubmitting = true);

    try {
      await widget.ref.read(jobsRepositoryProvider).submitJob(
        widget.type,
        widget.item['id'],
        imagePath: _selectedImage?.path,
        pdfPath: _selectedPdf?.path,
        taskFilePath: _selectedTaskFile?.path,
      );
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Đã gửi tài liệu thành công!'), backgroundColor: Colors.green));
        widget.ref.refresh(activeJobsProvider);
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString().replaceAll('Exception: ', '')), backgroundColor: Colors.red));
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final item = widget.item;
    final isOrder = widget.type == 'order';
    final status = item['status'];

    final String code = isOrder ? 'Mã Đơn: #${item['id']}' : 'Đơn: ${item['order']?['name'] ?? 'N/A'}';
    final double reward = double.tryParse(item['reward']?.toString() ?? '0') ?? 0;
    final formattedReward = NumberFormat.decimalPattern('vi_VN').format(reward);

    return InkWell(
      onTap: () {
        Navigator.push(context, MaterialPageRoute(builder: (context) => JobDetailScreen(type: widget.type, id: item['id'], isDesigner: widget.isDesigner)))
            .then((_) => widget.ref.refresh(activeJobsProvider));
      },
      borderRadius: BorderRadius.circular(20),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: Colors.grey.shade200),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.03), blurRadius: 10, offset: const Offset(0, 4))],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.all(16.0),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(code, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.grey)),
                        const SizedBox(height: 4),
                        Text(item['name'], style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold), maxLines: 2),
                      ],
                    ),
                  ),
                  _buildStatusBadge(status),
                ],
              ),
            ),

            if (status == 'rejected' && item['reject_reason'] != null)
              Container(
                margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.red.shade100)),
                child: Text('Sếp chú thích: ${item['reject_reason']}', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.red.shade700)),
              ),

            if (!widget.isDesigner && !isOrder && (status == 'processing' || status == 'rejected'))
              _buildProgressBar(item),

            if (status == 'processing' || status == 'rejected')
              Padding(
                padding: const EdgeInsets.all(16.0),
                child: widget.isDesigner
                    ? _buildDesignerUploadForm(isOrder)
                    : _buildWorkerActionButton(item['id']),
              ),

            if (status == 'pending_review')
              Padding(
                padding: const EdgeInsets.all(16.0),
                child: Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  decoration: BoxDecoration(color: Colors.orange.shade50, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.orange.shade100)),
                  child: Column(
                    children: [
                      SizedBox(width: 24, height: 24, child: CircularProgressIndicator(color: Colors.orange.shade400, strokeWidth: 2)),
                      const SizedBox(height: 8),
                      Text('Đang chờ xét duyệt...', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.orange.shade700, letterSpacing: 1)),
                    ],
                  ),
                ),
              ),

            if (!widget.isDesigner)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: BoxDecoration(color: Colors.grey.shade50, borderRadius: const BorderRadius.only(bottomLeft: Radius.circular(20), bottomRight: Radius.circular(20))),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Thù lao dự kiến:', style: TextStyle(fontSize: 12, color: Colors.grey, fontWeight: FontWeight.w600)),
                    Text('+$formattedReward đ', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: Colors.blue.shade600)),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildProgressBar(Map<String, dynamic> item) {
    int total = int.tryParse(item['item_count']?.toString() ?? '1') ?? 1;
    int completed = int.tryParse(item['completed_count']?.toString() ?? '0') ?? 0;
    double percent = total > 0 ? (completed / total) : 0.0;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 4.0),
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(color: Colors.grey.shade50, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.grey.shade200)),
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Tiến độ hiện tại', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.grey)),
                Text('$completed/$total', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w900, color: percent >= 1.0 ? Colors.green : Colors.blue.shade600)),
              ],
            ),
            const SizedBox(height: 8),
            LinearProgressIndicator(
              value: percent,
              backgroundColor: Colors.grey.shade300,
              color: percent >= 1.0 ? Colors.green : Colors.blue.shade500,
              minHeight: 8,
              borderRadius: BorderRadius.circular(4),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDesignerUploadForm(bool isOrder) {
    return Column(
      children: [
        if (isOrder)
          Row(
            children: [
              Expanded(
                child: InkWell(
                  onTap: _pickImage,
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    height: 70,
                    decoration: BoxDecoration(color: Colors.blue.shade50.withOpacity(0.5), border: Border.all(color: Colors.blue.shade200), borderRadius: BorderRadius.circular(12)),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.image_outlined, color: Colors.blue.shade500, size: 24),
                        const SizedBox(height: 4),
                        Text(_selectedImage != null ? '✓ Đã chọn ảnh' : 'Bản vẽ (Ảnh)', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: _selectedImage != null ? Colors.green : Colors.blue.shade700)),
                      ],
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: InkWell(
                  onTap: _pickPdf,
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    height: 70,
                    decoration: BoxDecoration(color: Colors.red.shade50.withOpacity(0.5), border: Border.all(color: Colors.red.shade200), borderRadius: BorderRadius.circular(12)),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.picture_as_pdf_outlined, color: Colors.red.shade500, size: 24),
                        const SizedBox(height: 4),
                        Text(_selectedPdf != null ? '✓ Đã chọn PDF' : 'Quy cách (PDF)', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: _selectedPdf != null ? Colors.green : Colors.red.shade700)),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          )
        else
          InkWell(
            onTap: _pickTaskFile,
            borderRadius: BorderRadius.circular(12),
            child: Container(
              height: 70,
              width: double.infinity,
              decoration: BoxDecoration(color: Colors.orange.shade50.withOpacity(0.5), border: Border.all(color: Colors.orange.shade200), borderRadius: BorderRadius.circular(12)),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.attach_file, color: Colors.orange.shade500, size: 24),
                  const SizedBox(height: 4),
                  Text(_selectedTaskFile != null ? '✓ Đã chọn file' : 'Tải lên Bản vẽ chi tiết', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: _selectedTaskFile != null ? Colors.green : Colors.orange.shade700)),
                ],
              ),
            ),
          ),
        const SizedBox(height: 12),
        SizedBox(
          width: double.infinity,
          child: ElevatedButton(
            onPressed: _isSubmitting ? null : _submitData,
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.blue.shade600,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 12),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            child: _isSubmitting
                ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                : const Text('GỬI DUYỆT TÀI LIỆU', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, letterSpacing: 1)),
          ),
        ),
      ],
    );
  }

  Widget _buildWorkerActionButton(int id) {
    return SizedBox(
      width: double.infinity,
      child: OutlinedButton.icon(
        onPressed: () {
          Navigator.push(context, MaterialPageRoute(builder: (context) => JobDetailScreen(type: widget.type, id: id, isDesigner: false)))
              .then((_) => widget.ref.refresh(activeJobsProvider));
        },
        icon: const Icon(Icons.arrow_forward_rounded, size: 16),
        label: const Text('CẬP NHẬT TIẾN ĐỘ', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, letterSpacing: 1)),
        style: OutlinedButton.styleFrom(
          foregroundColor: Colors.blue.shade700,
          side: BorderSide(color: Colors.blue.shade200, width: 2),
          padding: const EdgeInsets.symmetric(vertical: 12),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          backgroundColor: Colors.blue.shade50,
        ),
      ),
    );
  }

  Widget _buildStatusBadge(String status) {
    if (status == 'processing') {
      return Container(padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4), decoration: BoxDecoration(color: Colors.blue.shade50, borderRadius: BorderRadius.circular(8)), child: Text('ĐANG LÀM', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.blue.shade700)));
    } else if (status == 'pending_review') {
      return Container(padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4), decoration: BoxDecoration(color: Colors.orange.shade50, borderRadius: BorderRadius.circular(8)), child: Text('CHỜ DUYỆT', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.orange.shade700)));
    } else if (status == 'rejected') {
      return Container(padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4), decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(8)), child: Text('SỬA LẠI', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.red.shade700)));
    }
    return const SizedBox();
  }
}