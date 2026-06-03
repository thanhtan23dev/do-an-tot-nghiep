import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/api/api_client.dart';
import '../../core/api/api_error_handler.dart';
import '../../core/exceptions/api_exception.dart';

final jobsRepositoryProvider = Provider<JobsRepository>((ref) {
  return JobsRepository();
});

class JobsRepository {
  final _dio = ApiClient().dio;

  Future<Map<String, dynamic>> fetchNewJobs([String search = '']) async {
    final response = await _dio.get('/jobs/new', queryParameters: {
      if (search.isNotEmpty) 'search': search,
    }).catchApiError();

    if (response.statusCode == 200 && response.data['success'] == true) {
      return {
        'items': response.data['data']['data'],
        'type': response.data['type'],
      };
    }
    throw ApiException('Không thể tải danh sách công việc');
  }

  Future<void> acceptJob(String type, int id) async {
    await _dio.post('/jobs/$type/$id/accept').catchApiError();
  }

  Future<void> rejectJob(String type, int id) async {
    await _dio.post('/jobs/$type/$id/reject').catchApiError();
  }

  Future<Map<String, dynamic>> fetchActiveJobs([String search = '']) async {
    final response = await _dio.get('/jobs/active', queryParameters: {
      if (search.isNotEmpty) 'search': search,
    }).catchApiError();

    if (response.statusCode == 200 && response.data['success'] == true) {
      return response.data;
    }
    throw ApiException('Không thể tải danh sách công việc');
  }

  Future<void> submitJob(String type, int id, {String? imagePath, String? pdfPath, String? taskFilePath}) async {
    FormData formData = FormData();

    if (imagePath != null) {
      formData.files.add(MapEntry(
        'design_image[]',
        await MultipartFile.fromFile(imagePath, filename: imagePath.split('/').last),
      ));
    }

    if (pdfPath != null) {
      formData.files.add(MapEntry(
        'quy_cach[]',
        await MultipartFile.fromFile(pdfPath, filename: pdfPath.split('/').last),
      ));
    }

    if (taskFilePath != null) {
      formData.files.add(MapEntry(
        'task_files[]',
        await MultipartFile.fromFile(taskFilePath, filename: taskFilePath.split('/').last),
      ));
    }

    await _dio.post(
        '/jobs/$type/$id/submit',
        data: formData,
        options: Options(
            headers: {
              'Content-Type': 'multipart/form-data',
              'Accept': 'application/json',
            }
        )
    ).catchApiError();
  }

  Future<void> updateProgress(String type, int id, int addCount) async {
    await _dio.post(
      '/jobs/$type/$id/update-progress',
      data: {'add_count': addCount},
    ).catchApiError();
  }

  Future<Map<String, dynamic>> fetchCompletedJobs([String search = '']) async {
    final response = await _dio.get('/jobs/completed', queryParameters: {
      if (search.isNotEmpty) 'search': search,
    }).catchApiError();

    if (response.statusCode == 200 && response.data['success'] == true) {
      return response.data;
    }
    throw ApiException('Không thể tải lịch sử công việc');
  }

  Future<Map<String, dynamic>> fetchJobDetail(String type, int id) async {
    final response = await _dio.get('/jobs/$type/$id').catchApiError();

    if (response.statusCode == 200 && response.data['success'] == true) {
      return response.data['data'];
    }
    throw ApiException('Không thể tải chi tiết công việc');
  }
}