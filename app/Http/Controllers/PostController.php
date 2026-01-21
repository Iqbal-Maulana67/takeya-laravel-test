<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::active()
            ->with('user')
            ->paginate(20);

        return PostResource::collection($posts);
    }

    public function create()
    {
        return 'posts.create';
    }

    public function store(StorePostRequest $request)
    {
        $post = Post::create([
            'user_id' => Auth::id(),
            ...$request->validated(),
        ]);

        return response()->json(
            new PostResource($post),
            201
        );
    }

    public function show(Post $post)
    {
        if (! $post->is_draft && $post->published_at <= now()) {
            return new PostResource(
                $post->load('user')
            );
        }

        abort(404);
    }

    public function edit(Post $post)
    {
        Gate::authorize('update', $post);
        return 'posts.edit';
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        Gate::authorize('update', $post);

        $post->update($request->validated());

        return response()->json(
            new PostResource($post)
        );
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
