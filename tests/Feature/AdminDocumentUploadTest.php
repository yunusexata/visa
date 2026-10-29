<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('validation rejects document uploads exceeding 3MB', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Mock storage disk
    Storage::fake('local');

    // Create a dummy file larger than 3MB (e.g. 4MB = 4096KB)
    $largeFile = UploadedFile::fake()->create('document.png', 4096, 'image/png');

    // Try posting the large file to each type
    $response = $this->post(route('admin.documents.upload'), [
        'ichijikin_files' => [$largeFile],
    ]);

    $response->assertSessionHasErrors([
        'ichijikin_files.0',
    ]);

    // Check that we get our custom error message
    $errors = session('errors')->get('ichijikin_files.0');
    expect($errors[0])->toBe('Ukuran file Ichijikin tidak boleh lebih dari 3MB.');
});

test('validation accepts document uploads within 3MB', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Mock storage disk
    Storage::fake('local');

    // Create a dummy file within 3MB (e.g. 2MB = 2048KB)
    $validFile = UploadedFile::fake()->create('1_John Doe.png', 2048, 'image/png');

    $response = $this->post(route('admin.documents.upload'), [
        'ichijikin_files' => [$validFile],
    ]);

    // It should NOT have validation errors on 'ichijikin_files'
    $response->assertSessionDoesntHaveErrors(['ichijikin_files.0']);
});
