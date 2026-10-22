<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('local');

    config([
        'laravel-imgproxy.instances.internal.url' => 'http://imgproxy:8080',
        'laravel-imgproxy.instances.default.url' => 'http://localhost:8080',
    ]);
});

test('uploaded image is optimized by imgproxy', function () {
    Http::fake(['imgproxy:8080/*' => Http::response('optimized')]);

    $response = $this->actingAs(User::factory()->create())->postJson('/api/v1/upload', [
        'entity' => 'avatars',
        'image' => UploadedFile::fake()->image('photo.jpg', 4000, 3000),
    ]);

    $response->assertOk()->assertJsonPath('ok', true);

    $path = $response->json('path');

    expect($path)->toEndWith('.webp');
    expect(Storage::disk('public')->get($path))->toBe('optimized');
    expect(Storage::disk('local')->files('uploads-tmp'))->toBeEmpty();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/rs:fit/w:2560/h:2560/'));
});

test('original image is stored when optimization is disabled', function () {
    config(['uploads.optimize' => false]);
    Http::fake();

    $response = $this->actingAs(User::factory()->create())->postJson('/api/v1/upload', [
        'entity' => 'avatars',
        'image' => UploadedFile::fake()->image('photo.png', 100, 100),
    ]);

    $response->assertOk();

    expect($response->json('path'))->toEndWith('.png');
    Storage::disk('public')->assertExists($response->json('path'));
    Http::assertNothingSent();
});

test('imgproxy failure returns validation error', function () {
    Http::fake(['imgproxy:8080/*' => Http::response('', 422)]);

    $this->actingAs(User::factory()->create())->postJson('/api/v1/upload', [
        'entity' => 'avatars',
        'image' => UploadedFile::fake()->image('photo.jpg'),
    ])->assertUnprocessable()->assertJsonPath('errors.0.name', 'image');

    expect(Storage::disk('local')->files('uploads-tmp'))->toBeEmpty();
});

test('non image files are rejected', function () {
    $this->actingAs(User::factory()->create())->postJson('/api/v1/upload', [
        'entity' => 'avatars',
        'image' => UploadedFile::fake()->create('file.pdf', 10, 'application/pdf'),
    ])->assertUnprocessable()->assertJsonPath('errors.0.name', 'image');
});

test('avatar url is signed imgproxy url for uploaded avatars', function () {
    $user = User::factory()->make(['avatar' => 'avatars/test.webp']);

    expect($user->avatar_url)->toStartWith('http://localhost:8080/')->toContain('/rs:fill/w:300/h:300/');

    $user->avatar = 'https://example.com/a.png';
    expect($user->avatar_url)->toBe('https://example.com/a.png');
});
