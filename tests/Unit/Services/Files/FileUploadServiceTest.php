<?php

namespace Tests\Unit\Services\Files;

use Tests\TestCase;
use App\Modules\Files\Services\FileUploadService;
use App\Modules\Files\Models\File;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadServiceTest extends TestCase
{
    use RefreshDatabase;

    private FileUploadService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new FileUploadService();
        
        $this->user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        // Fake storage for testing
        Storage::fake('public');
    }

    public function test_upload_valid_file_successfully()
    {
        $file = UploadedFile::fake()->image('test.jpg', 100, 100)->size(1000); // 1MB

        $uploadedFile = $this->service->upload($file, $this->user);

        $this->assertInstanceOf(File::class, $uploadedFile);
        $this->assertEquals($this->user->id, $uploadedFile->user_id);
        $this->assertEquals('image/jpeg', $uploadedFile->mime_type);
        $this->assertEquals(1024000, $uploadedFile->size); // 1MB in bytes
        $this->assertFalse($uploadedFile->is_public);
        
        // Check file was stored
        Storage::disk('public')->assertExists($uploadedFile->path);
    }

    public function test_upload_file_with_module_info()
    {
        $file = UploadedFile::fake()->create('document.pdf', 500);

        $uploadedFile = $this->service->upload(
            $file, 
            $this->user, 
            'users', 
            123, 
            true
        );

        $this->assertEquals('users', $uploadedFile->module);
        $this->assertEquals(123, $uploadedFile->module_id);
        $this->assertTrue($uploadedFile->is_public);
        $this->assertStringContains('uploads/users/123/', $uploadedFile->path);
    }

    public function test_upload_rejects_oversized_file()
    {
        $file = UploadedFile::fake()->create('large.pdf', 11 * 1024); // 11MB

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('حجم الملف يتجاوز الحد المسموح به (10 ميجابايت)');

        $this->service->upload($file, $this->user);
    }

    public function test_upload_rejects_invalid_mime_type()
    {
        $file = UploadedFile::fake()->create('script.exe', 100);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('نوع الملف غير مسموح به');

        $this->service->upload($file, $this->user);
    }

    public function test_upload_accepts_allowed_mime_types()
    {
        $allowedFiles = [
            UploadedFile::fake()->image('image.jpg'),
            UploadedFile::fake()->image('image.png'),
            UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->create('document.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            UploadedFile::fake()->create('text.txt', 100, 'text/plain'),
        ];

        foreach ($allowedFiles as $file) {
            $uploadedFile = $this->service->upload($file, $this->user);
            $this->assertInstanceOf(File::class, $uploadedFile);
        }
    }

    public function test_delete_file_successfully()
    {
        // First upload a file
        $file = UploadedFile::fake()->image('test.jpg');
        $uploadedFile = $this->service->upload($file, $this->user);
        
        // Verify file exists
        Storage::disk('public')->assertExists($uploadedFile->path);
        $this->assertDatabaseHas('files', ['id' => $uploadedFile->id]);

        // Delete the file
        $result = $this->service->delete($uploadedFile);

        $this->assertTrue($result);
        Storage::disk('public')->assertMissing($uploadedFile->path);
        $this->assertDatabaseMissing('files', ['id' => $uploadedFile->id]);
    }

    public function test_delete_nonexistent_file_still_removes_database_record()
    {
        // Create file record without actual file
        $fileRecord = File::create([
            'name' => 'test.jpg',
            'original_name' => 'test.jpg',
            'path' => 'nonexistent/path.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 1000,
            'user_id' => $this->user->id,
            'is_public' => false,
        ]);

        $result = $this->service->delete($fileRecord);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('files', ['id' => $fileRecord->id]);
    }

    public function test_upload_generates_unique_filename()
    {
        $file1 = UploadedFile::fake()->image('same_name.jpg');
        $file2 = UploadedFile::fake()->image('same_name.jpg');

        $uploadedFile1 = $this->service->upload($file1, $this->user);
        $uploadedFile2 = $this->service->upload($file2, $this->user);

        $this->assertNotEquals($uploadedFile1->name, $uploadedFile2->name);
        $this->assertEquals('same_name.jpg', $uploadedFile1->original_name);
        $this->assertEquals('same_name.jpg', $uploadedFile2->original_name);
    }

    public function test_upload_preserves_original_filename()
    {
        $file = UploadedFile::fake()->image('my_special_file.jpg');

        $uploadedFile = $this->service->upload($file, $this->user);

        $this->assertEquals('my_special_file.jpg', $uploadedFile->original_name);
        $this->assertNotEquals('my_special_file.jpg', $uploadedFile->name);
    }
}
