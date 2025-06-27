<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Traits\AuthApiTrait;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class PostController extends Controller
{
    use AuthApiTrait;

    public function index(Request $request)
    {
        $query = Post::query();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        $sort = $request->get('sort', 'desc');
        $query->orderBy('created_at', $sort);

        $user = JWTAuth::user();

        if (!$user || $user->role !== 'admin') {
            $query->where('is_archived', false);
        }

        $posts = $query->get();

        return $this->successResponse($posts, 'Posts retrieved successfully');
    }

    public function store(Request $request)
    {
        $user = JWTAuth::user();

        if (!$user) {
            return $this->successResponse(null, 'Unauthorized', 401);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_archived' => 'boolean',
        ]);

        $data['user_id'] = $user->id;

        $post = Post::create($data);

        return $this->successResponse($post, 'Post created successfully', 201);
    }

    public function show($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return $this->successResponse(null, 'Post not found', 404);
        }

        $user = JWTAuth::user();

        if ($post->is_archived && (!$user || $user->role !== 'admin')) {
            return $this->successResponse(null, 'Unauthorized', 403);
        }

        return $this->successResponse($post, 'Post retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return $this->successResponse(null, 'Post not found', 404);
        }

        $user = JWTAuth::user();

        if (!$user || ($user->role !== 'admin' && $post->user_id !== $user->id)) {
            return $this->successResponse(null, 'Forbidden', 403);
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'is_archived' => 'sometimes|boolean',
        ]);

        $post->update($data);

        return $this->successResponse($post, 'Post updated successfully');
    }

    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return $this->successResponse(null, 'Post not found', 404);
        }

        $user = JWTAuth::user();

        if (!$user || ($user->role !== 'admin' && $post->user_id !== $user->id)) {
            return $this->successResponse(null, 'Forbidden', 403);
        }

        $post->delete();

        return $this->successResponse(null, 'Post deleted successfully', 204);
    }
}
