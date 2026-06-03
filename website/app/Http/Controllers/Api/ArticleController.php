<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::where('status', 'PUBLISHED')
            ->select('id', 'title', 'excerpt', 'image', 'views', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $articles->getCollection()->transform(function ($article) {
            $article->image_url = $article->image ? asset('storage/' . $article->image) : null;
            return $article;
        });

        return response()->json($articles);
    }

    public function show($id)
    {
        $article = Article::where('status', 'PUBLISHED')->find($id);

        if (!$article) {
            return response()->json(['message' => 'Không tìm thấy bài viết'], 404);
        }

        $article->increment('views');
        $article->image_url = $article->image ? asset('storage/' . $article->image) : null;

        return response()->json($article);
    }
}