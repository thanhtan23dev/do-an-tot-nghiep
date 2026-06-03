class Article {
  final int id;
  final String title;
  final String? excerpt;
  final String? body;
  final String? imageUrl;
  final int views;
  final String createdAt;

  Article({
    required this.id,
    required this.title,
    this.excerpt,
    this.body,
    this.imageUrl,
    required this.views,
    required this.createdAt,
  });

  factory Article.fromJson(Map<String, dynamic> json) {
    return Article(
      id: int.tryParse(json['id'].toString()) ?? 0,
      title: json['title'] ?? '',
      excerpt: json['excerpt'],
      body: json['body'],
      imageUrl: json['image_url'],
      views: int.tryParse(json['views'].toString()) ?? 0,
      createdAt: json['created_at'] ?? '',
    );
  }
}