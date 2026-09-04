<?php

use App\Actions\Post\CreatePostAction;
use App\Actions\Post\UpdatePostAction;
use App\Actions\Post\DeletePostAction;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/* ==========================================================================
   CREATE POST ACTION
   ========================================================================== */

test('CreatePostAction successfully creates and stores a post in the database', function () {
    $user = User::factory()->create();
    $data = [
        'title' => 'Action test title',
        'content' => 'Action test content body.',
    ];

    $action = new CreatePostAction();
    $returnedPost = $action->execute($data, $user);

    expect($returnedPost)->toBeInstanceOf(Post::class);
    expect($returnedPost->title)->toBe('Action test title');
    expect($returnedPost->user_id)->toBe($user->id);

    $this->assertDatabaseHas('posts', [
        'title' => 'Action test title',
        'content' => 'Action test content body.',
        'user_id' => $user->id,
    ]);
});

/* ==========================================================================
   UPDATE POST ACTION
   ========================================================================== */

test('UpdatePostAction successfully updates an existing post', function () {
    $post = Post::factory()->create([
        'title' => 'Old Title',
        'content' => 'Old Content',
    ]);

    $updateData = [
        'title' => 'Brand New Title',
        'content' => 'Updated Content Body.',
    ];

    $action = new UpdatePostAction();
    $updatedPost = $action->execute($updateData, $post);

    expect($updatedPost)->toBeInstanceOf(Post::class);
    expect($updatedPost->title)->toBe('Brand New Title');

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'Brand New Title',
        'content' => 'Updated Content Body.',
    ]);

    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
        'title' => 'Old Title',
    ]);
});

/* ==========================================================================
   DELETE POST ACTION
   ========================================================================== */

test('DeletePostAction successfully removes a post from the database', function () {
    $post = Post::factory()->create();

    $action = new DeletePostAction();
    $action->execute($post);

    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
    ]);
});
