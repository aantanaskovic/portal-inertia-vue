<?php

namespace App\Http\Controllers\Inertia;

use App\Actions\Post\CreatePostAction;
use App\Actions\Post\DeletePostAction;
use App\Actions\Post\UpdatePostAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostFormRequest;
use App\Http\Requests\UpdatePostFormRequest;
use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostController extends Controller
{
    private const int POSTS_PER_PAGE = 10;

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Post::class);

        $posts = Post::forIndex()
            ->paginate(static::POSTS_PER_PAGE)
            ->withQueryString();

        return Inertia::render('Posts/Index', [
            'posts' => PostResource::collection($posts)
        ]);
    }

    public function create()
    {
        Gate::authorize('create', Post::class);

        return Inertia::render('Posts/Create');
    }

    public function store(StorePostFormRequest $request, CreatePostAction $action)
    {
        $action->execute($request->validated(), $request->user());

        return redirect()->route('posts.index')->with('success', 'Post created successfully.');
    }

    public function show(Request $request, Post $post)
    {
        Gate::authorize('view', $post);

        return Inertia::render('Posts/Show', [
            'post' => PostResource::make($post)
        ]);
    }

    public function edit(Request $request, Post $post)
    {
        Gate::authorize('update', $post);

        $redirectTo = $request->input('redirect_to', route('posts.index'));

        return Inertia::render('Posts/Edit', [
            'post' => PostResource::make($post),
            'redirect_to' => $redirectTo,
        ]);
    }

    public function update(UpdatePostFormRequest $request, Post $post, UpdatePostAction $action)
    {
        $action->execute($request->validated(), $post);

        $redirectTo = $request->input('redirect_to', route('posts.index'));

        try {
            $originalRequest = Request::create($redirectTo);
            $route = Route::getRoutes()->match($originalRequest);
            $redirectRouteName = $route->getName();
        } catch (NotFoundHttpException $e) {
            $originalRequest = null;
            $redirectRouteName = 'posts.index';
        }

        if ($redirectRouteName !== 'posts.index') {
            return redirect()->route($redirectRouteName)->with('success', 'Post updated successfully.');
        }

        $query = Post::forIndex($originalRequest);

        $allIds = $query->pluck('id')->toArray();
        $postIndex = array_search($post->id, $allIds);
        $countBefore = ($postIndex !== false) ? $postIndex : 0;

        $page = floor($countBefore / static::POSTS_PER_PAGE) + 1;

        $existingParams = $originalRequest ? $originalRequest->query() : [];
        $redirectParams = array_merge($existingParams, ['page' => $page]);

        return redirect()->route('posts.index', $redirectParams)
            ->with('success', 'Post updated successfully.')
            ->with('updated_post_id', $post->id);
    }

    public function destroy(Post $post, DeletePostAction $action)
    {
        Gate::authorize('delete', $post);

        $action->execute($post);

        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }
}
