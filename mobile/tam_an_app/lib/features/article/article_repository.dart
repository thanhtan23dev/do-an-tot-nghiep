import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/api/api_client.dart';
import '../../core/api/api_error_handler.dart';
import 'article.dart';

final articleRepositoryProvider = Provider<ArticleRepository>((ref) {
  return ArticleRepository();
});

class ArticleRepository {
  final _dio = ApiClient().dio;

  Future<List<Article>> fetchArticles() async {
    final response = await _dio.get('/articles').catchApiError();

    if (response.statusCode == 200) {
      final List<dynamic> data = response.data['data'];
      return data.map((json) => Article.fromJson(json)).toList();
    }
    throw Exception('Không thể tải danh sách bài viết');
  }
  Future<Article> fetchArticleDetail(int id) async {
    try {
      final response = await _dio.get('/articles/$id');
      if (response.statusCode == 200) {
        return Article.fromJson(response.data);
      } else {
        throw Exception('Không thể tải chi tiết bài viết');
      }
    } catch (e) {
      throw Exception('Lỗi kết nối: ${e.toString()}');
    }
  }
}