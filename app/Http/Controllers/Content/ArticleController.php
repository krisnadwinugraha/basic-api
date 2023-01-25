<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $data = Article::paginate(request()->input('results', 3));    
        return response()->json($data, 200);       
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
            'content' => 'required'
        ]);

        $article = new Article;
        $article->title = $request->title;
        $article->description = $request->description;
        $article->content = $request->content;

        $article->save();

        return response()->json([
            'message' => 'Article created successfully.'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);
        $article->update([
            'title'     => $request->title,
            'description'   => $request->description,
            'content' => $request->content
        ]);

        return response()->json([
            'message' => 'Article updated successfully.'
        ], 200);
    }

    public function delete($id)
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return response()->json([
            'message' => 'Article deleted successfully.'
        ], 200);
    }
}
