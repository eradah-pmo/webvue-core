<?php

namespace Tests\Feature\Modules\Files;

use Tests\TestCase;
use App\Modules\Files\Models\File;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class FilesControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => bcrypt('password')
        ]);

        $this->adminUser = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password')
        ]);

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'super-admin']);
        $this->adminUser->assignRole($adminRole);

        // Fake storage for testing
        Storage::fake('public');
    }

    public function test_index_requires_authentication()
    {
        $response = $this->getJson('/api/v1/files');
        $response->assertStatus(401);
    }

    public function test_index_displays_files_list()
    {
        // Create test files
        File::create([
            'name' => 'test1.jpg',
            'original_name' => 'test1.jpg',
            'path' => 'uploads/test1.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 1000,
            'user_id' => $this->user->id,
            'is_public' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/files');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'original_name',
                        'path',
                        'mime_type',
                        'size',
                        'user_id',
                        'is_public',
                        'created_at'
                    ]
                ]
            ]);
    }

    public function test_store_uploads_file_successfully()
    {
        $file = UploadedFile::fake()->image('test.jpg', 100, 100)->size(1000);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/files/upload', [
                'file' => $file,
                'module' => 'users',
                'module_id' => 123,
                'is_public' => true
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'تم رفع الملف بنجاح'
            ])
            ->assertJsonStructure([
                'file' => [
                    'id',
                    'name',
                    'original_name',
                    'module',
                    'module_id',
                    'is_public'
                ]
            ]);

        $this->assertDatabaseHas('files', [
            'original_name' => 'test.jpg',
            'module' => 'users',
            'module_id' => 123,
            'is_public' => true,
            'user_id' => $this->user->id
        ]);
    }

    public function test_store_rejects_oversized_file()
    {
        $file = UploadedFile::fake()->create('large.pdf', 11 * 1024); // 11MB

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/files/upload', [
                'file' => $file
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false
            ]);
    }

    public function test_store_rejects_invalid_file_type()
    {
        $file = UploadedFile::fake()->create('script.exe', 100);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/files/upload', [
                'file' => $file
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false
            ]);
    }

    public function test_show_displays_file_details()
    {
        $file = File::create([
            'name' => 'test.jpg',
            'original_name' => 'test.jpg',
            'path' => 'uploads/test.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 1000,
            'user_id' => $this->user->id,
            'is_public' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/files/{$file->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $file->id,
                'name' => 'test.jpg',
                'original_name' => 'test.jpg'
            ]);
    }

    public function test_download_public_file_without_authentication()
    {
        $file = File::create([
            'name' => 'public.jpg',
            'original_name' => 'public.jpg',
            'path' => 'uploads/public.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 1000,
            'user_id' => $this->user->id,
            'is_public' => true,
        ]);

        // Create fake file in storage
        Storage::disk('public')->put($file->path, 'fake file content');

        $response = $this->get("/files/{$file->id}/download");

        $response->assertStatus(200);
    }

    public function test_download_private_file_requires_owner_or_admin()
    {
        $file = File::create([
            'name' => 'private.jpg',
            'original_name' => 'private.jpg',
            'path' => 'uploads/private.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 1000,
            'user_id' => $this->user->id,
            'is_public' => false,
        ]);

        // Create fake file in storage
        Storage::disk('public')->put($file->path, 'fake file content');

        // Test unauthorized access
        $otherUser = User::create([
            'first_name' => 'Other',
            'last_name' => 'User',
            'name' => 'Other User',
            'email' => 'other@test.com',
            'password' => bcrypt('password')
        ]);

        $response = $this->actingAs($otherUser)
            ->get("/files/{$file->id}/download");

        $response->assertStatus(403);

        // Test owner access
        $response = $this->actingAs($this->user)
            ->get("/files/{$file->id}/download");

        $response->assertStatus(200);

        // Test admin access
        $response = $this->actingAs($this->adminUser)
            ->get("/files/{$file->id}/download");

        $response->assertStatus(200);
    }

    public function test_destroy_deletes_file_successfully()
    {
        $file = File::create([
            'name' => 'delete_me.jpg',
            'original_name' => 'delete_me.jpg',
            'path' => 'uploads/delete_me.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 1000,
            'user_id' => $this->user->id,
            'is_public' => false,
        ]);

        // Create fake file in storage
        Storage::disk('public')->put($file->path, 'fake file content');

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/files/{$file->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'تم حذف الملف بنجاح'
            ]);

        $this->assertDatabaseMissing('files', ['id' => $file->id]);
        Storage::disk('public')->assertMissing($file->path);
    }

    public function test_destroy_requires_owner_or_admin_permission()
    {
        $file = File::create([
            'name' => 'protected.jpg',
            'original_name' => 'protected.jpg',
            'path' => 'uploads/protected.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 1000,
            'user_id' => $this->user->id,
            'is_public' => false,
        ]);

        $otherUser = User::create([
            'first_name' => 'Other',
            'last_name' => 'User',
            'name' => 'Other User',
            'email' => 'other@test.com',
            'password' => bcrypt('password')
        ]);

        // Test unauthorized deletion
        $response = $this->actingAs($otherUser, 'sanctum')
            ->deleteJson("/api/v1/files/{$file->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('files', ['id' => $file->id]);
    }

    public function test_index_filters_by_module()
    {
        File::create([
            'name' => 'user_file.jpg',
            'original_name' => 'user_file.jpg',
            'path' => 'uploads/user_file.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 1000,
            'user_id' => $this->user->id,
            'module' => 'users',
            'is_public' => false,
        ]);

        File::create([
            'name' => 'dept_file.jpg',
            'original_name' => 'dept_file.jpg',
            'path' => 'uploads/dept_file.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 1000,
            'user_id' => $this->user->id,
            'module' => 'departments',
            'is_public' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/files?module=users');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(1, $data);
        $this->assertEquals('users', $data[0]['module']);
    }
}
