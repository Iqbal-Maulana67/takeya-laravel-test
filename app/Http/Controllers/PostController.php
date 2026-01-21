<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')
            ->where('is_draft', false)
            ->where('published_at', '<=', Carbon::now())
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Posts retrieved successfully',
            'data' => $posts
        ]);
    }

    public function create()
    {
        return 'posts.create';
    }

    public function store(StorePostRequest $request)
    {
        $post = Post::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'content' => $request->content,
            'is_draft' => $request->boolean('is_draft'),
            'published_at' => $request->published_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
            'data' => $post,
        ], 201);
    }

    public function show($id)
    {
        $post = Post::with('user')
            ->where('id', $id)
            ->where('is_draft', false)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $post,
        ]);
    }

    public function edit(Post $post)
    {
        Gate::authorize('update', $post);
        return 'posts.edit';
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        Gate::authorize('update', $post);

        $post->update($request->only(['title', 'content', 'is_draft', 'published_at']));

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'data' => $post,
        ]);
    }

    public function destroy(Post $post)
    {
        Gate::authorize('delete', $post);

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
        ]);
    }
}
