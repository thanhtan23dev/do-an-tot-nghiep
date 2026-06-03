import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'article/article.dart';
import 'article/article_repository.dart';

class ArticleDetailScreen extends ConsumerWidget {
  final Article article;

  const ArticleDetailScreen({super.key, required this.article});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detailFuture = ref.read(articleRepositoryProvider).fetchArticleDetail(article.id);

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.black87),
        actions: [
          IconButton(icon: const Icon(Icons.share_outlined), onPressed: () {}),
        ],
      ),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
              child: Text(
                article.title,
                style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w900, height: 1.3, color: Colors.black87),
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16.0),
              child: Row(
                children: [
                  CircleAvatar(radius: 20, backgroundColor: Colors.blue.shade50, child: Icon(Icons.architecture, color: Colors.blue.shade600)),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Ban biên tập Tâm An', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                        const SizedBox(height: 2),
                        Text('${article.createdAt.substring(0, 10)} • ${article.views} lượt xem', style: const TextStyle(color: Colors.grey, fontSize: 13)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            if (article.imageUrl != null)
              Image.network(
                article.imageUrl!,
                width: double.infinity,
                fit: BoxFit.cover,
                errorBuilder: (context, error, stackTrace) => const SizedBox.shrink(),
              ),

            if (article.excerpt != null && article.excerpt!.isNotEmpty)
              Padding(
                padding: const EdgeInsets.all(16.0),
                child: Text(
                  article.excerpt!,
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600, fontStyle: FontStyle.italic, color: Color(0xFF4B5563), height: 1.5),
                ),
              ),

            FutureBuilder<Article>(
              future: detailFuture,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const Padding(
                    padding: EdgeInsets.all(32.0),
                    child: Center(child: CircularProgressIndicator()),
                  );
                } else if (snapshot.hasError) {
                  return Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Text('Lỗi tải nội dung: ${snapshot.error}', style: const TextStyle(color: Colors.red)),
                  );
                } else if (snapshot.hasData && snapshot.data!.body != null) {
                  return Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 8.0),
                    child: Html(
                      data: snapshot.data!.body!,
                      style: {
                        "body": Style(fontSize: FontSize(16.0), color: const Color(0xFF1F2937), lineHeight: LineHeight(1.6), margin: Margins.zero),
                        "p": Style(margin: Margins.only(bottom: 16.0)),
                        "img": Style(width: Width(100, Unit.percent)),
                      },
                    ),
                  );
                } else {
                  return const SizedBox.shrink();
                }
              },
            ),

            const SizedBox(height: 40),
          ],
        ),
      ),
    );
  }
}