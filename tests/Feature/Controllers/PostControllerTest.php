<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create();
    $this->actingAs($user);
});

/* ==========================================================================
   INDEX METHOD
   ========================================================================== */

test('index method renders the correct Inertia component with paginated posts', function () {
    Gate::before(fn() => true);

    Post::factory()->count(15)->create();

    $response = $this->get(route('posts.index'));

    $response->assertStatus(200);
    $response->assertInertia(
        fn(Assert $page) => $page
            ->component('Posts/Index')
            ->has('posts.data', 10)
            ->has('posts.meta')
    );
});

test('index method is protected by authorization', function () {
    Gate::before(fn() => false);

    $this->get(route('posts.index'))->assertStatus(403);
});

/* ==========================================================================
   CREATE & STORE METHODS
   ========================================================================== */

test('create method renders the correct Inertia component', function () {
    Gate::before(fn() => true);

    $this->get(route('posts.create'))
        ->assertStatus(200)
        ->assertInertia(fn(Assert $page) => $page->component('Posts/Create'));
});

test('create method is protected by authorization', function () {
    Gate::before(fn() => false);

    $this->get(route('posts.create'))->assertStatus(403);
});

test('store method successfully creates a post and redirects to index', function () {
    Gate::before(fn() => true);

    $postData = [
        'title' => 'New test post',
        'content' => 'Content of test post.',
    ];

    $response = $this->post(route('posts.store'), $postData);

    $response->assertRedirect(route('posts.index'));
    $response->assertSessionHas('success', 'Post created successfully.');

    $this->assertDatabaseHas('posts', [
        'title' => 'New test post'
    ]);
});

test('store method fails validation when required fields are missing', function () {
    Gate::before(fn() => true);

    $response = $this->post(route('posts.store'), [
        'title' => '',
        'content' => '',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['title', 'content']);
});

/* ==========================================================================
   SHOW & EDIT METHODS
   ========================================================================== */

test('show method renders the correct component with a single post', function () {
    Gate::before(fn() => true);

    $post = Post::factory()->create();

    $this->get(route('posts.show', $post))
        ->assertStatus(200)
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('Posts/Show')
                ->has('post')
                ->where('post.id', $post->id)
        );
});

test('show method is protected by authorization', function () {
    Gate::before(fn() => false);

    $post = Post::factory()->create();

    $this->get(route('posts.show', $post))->assertStatus(403);
});

test('edit method renders the edit form with the correct redirect_to parameter', function () {
    Gate::before(fn() => true);

    $post = Post::factory()->create();
    $customRedirect = 'http://localhost/some-other-page';

    $this->get(route('posts.edit', [$post, 'redirect_to' => $customRedirect]))
        ->assertStatus(200)
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('Posts/Edit')
                ->where('post.id', $post->id)
                ->where('redirect_to', $customRedirect)
        );
});

test('edit method is protected by authorization', function () {
    Gate::before(fn() => false);

    $post = Post::factory()->create();

    $this->get(route('posts.edit', $post))->assertStatus(403);
});

/* ==========================================================================
   UPDATE METHOD
   ========================================================================== */

test('update method redirects to a custom route if redirect_to is not posts.index', function () {
    Gate::before(fn() => true);

    if (!Route::has('dashboard')) {
        Route::get('/dashboard', fn() => 'dashboard')->name('dashboard');
    }

    $post = Post::factory()->create();
    $customRedirect = route('dashboard');

    $response = $this->put(route('posts.update', $post), [
        'title' => 'Modified title',
        'content' => 'Modified content',
        'redirect_to' => $customRedirect
    ]);

    $response->assertRedirect($customRedirect);
    $response->assertSessionHas('success', 'Post updated successfully.');
});

test('update method calculates the correct page and redirects back to posts.index', function () {
    Gate::before(fn() => true);

    for ($i = 1; $i <= 25; $i++) {
        Post::factory()->create([
            'id' => $i,
            'created_at' => now()->subDays(30)->addMinutes($i),
        ]);
    }

    $targetPost = Post::find(12);

    $response = $this->put(route('posts.update', $targetPost), [
        'title' => 'Modified title on second page',
        'content' => 'Modified content',
        'redirect_to' => route('posts.index')
    ]);

    $response->assertRedirect(route('posts.index', ['page' => 2]));
    $response->assertSessionHas('success', 'Post updated successfully.');
    $response->assertSessionHas('updated_post_id', $targetPost->id);
});

test('update method falls back to posts.index if redirect_to path does not exist', function () {
    Gate::before(fn() => true);

    $post = Post::factory()->create();

    $invalidRedirect = 'http://localhost/non-existent-route-xyz';

    $response = $this->put(route('posts.update', $post), [
        'title' => 'Modified title',
        'content' => 'Modified content',
        'redirect_to' => $invalidRedirect
    ]);

    $response->assertRedirect(route('posts.index', ['page' => 1]));
    $response->assertSessionHas('success', 'Post updated successfully.');
});

test('update method is protected by authorization', function () {
    Gate::before(fn() => false);

    $post = Post::factory()->create();

    $response = $this->put(route('posts.update', $post), [
        'title' => 'Unauthorized Title',
        'content' => 'Unauthorized Content',
    ]);

    $response->assertStatus(403);

    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
        'title' => 'Unauthorized Title'
    ]);
});

/* ==========================================================================
   DESTROY METHOD
   ========================================================================== */

test('destroy method deletes the post and redirects to index', function () {
    Gate::before(fn() => true);

    $post = Post::factory()->create();

    $response = $this->delete(route('posts.destroy', $post));

    $response->assertRedirect(route('posts.index'));
    $response->assertSessionHas('success', 'Post deleted successfully.');

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});

test('destroy method is protected by authorization', function () {
    Gate::before(fn() => false);

    $post = Post::factory()->create();

    $response = $this->delete(route('posts.destroy', $post));

    $response->assertStatus(403);
    $this->assertDatabaseHas('posts', ['id' => $post->id]);
});
