import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'article.dart';
import 'article_repository.dart';

final articlesProvider = FutureProvider.autoDispose<List<Article>>((ref) async {
  final repository = ref.read(articleRepositoryProvider);
  return await repository.fetchArticles();
});