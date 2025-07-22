<?php

namespace Tests\Unit\Services\Settings;

use Tests\TestCase;
use App\Modules\Settings\Services\SettingsFileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingsFileServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SettingsFileService $fileService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->fileService = app(SettingsFileService::class);
        Storage::fake('public');
    }

    /** @test */
    public function it_can_upload_file()
    {
        $file = UploadedFile::fake()->image('logo.jpg', 100, 100);
        
        $path = $this->fileService->uploadFile($file, 'site_logo');
        
        $this->assertNotNull($path);
        $this->assertStringContains('settings/', $path);
        Storage::disk('public')->assertExists($path);
    }

    /** @test */
    public function it_can_delete_existing_file()
    {
        // إنشاء ملف وهمي
        $file = UploadedFile::fake()->image('test.jpg');
        $path = $this->fileService->uploadFile($file, 'test_key');
        
        Storage::disk('public')->assertExists($path);
        
        // حذف الملف
        $result = $this->fileService->deleteFile($path);
        
        $this->assertTrue($result);
        Storage::disk('public')->assertMissing($path);
    }

    /** @test */
    public function it_handles_non_existent_file_deletion()
    {
        $result = $this->fileService->deleteFile('non/existent/file.jpg');
        
        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_check_file_existence()
    {
        $file = UploadedFile::fake()->image('exists.jpg');
        $path = $this->fileService->uploadFile($file, 'exists_test');
        
        $this->assertTrue($this->fileService->fileExists($path));
        $this->assertFalse($this->fileService->fileExists('non/existent.jpg'));
    }

    /** @test */
    public function it_can_get_file_url()
    {
        $file = UploadedFile::fake()->image('url_test.jpg');
        $path = $this->fileService->uploadFile($file, 'url_test');
        
        $url = $this->fileService->getFileUrl($path);
        
        $this->assertStringContains('/storage/', $url);
        $this->assertStringContains($path, $url);
    }

    /** @test */
    public function it_returns_null_for_empty_path_url()
    {
        $url = $this->fileService->getFileUrl('');
        $this->assertNull($url);
        
        $url = $this->fileService->getFileUrl(null);
        $this->assertNull($url);
    }

    /** @test */
    public function it_validates_file_type()
    {
        // ملف صورة صالح
        $imageFile = UploadedFile::fake()->image('valid.jpg');
        $imagePath = $this->fileService->uploadFile($imageFile, 'image_test');
        $this->assertNotNull($imagePath);
        
        // ملف غير صالح (نص)
        $textFile = UploadedFile::fake()->create('invalid.txt', 100);
        $textPath = $this->fileService->uploadFile($textFile, 'text_test');
        $this->assertNull($textPath);
    }

    /** @test */
    public function it_validates_file_size()
    {
        // ملف صغير صالح
        $smallFile = UploadedFile::fake()->image('small.jpg', 100, 100)->size(500); // 500KB
        $smallPath = $this->fileService->uploadFile($smallFile, 'small_test');
        $this->assertNotNull($smallPath);
        
        // ملف كبير غير صالح
        $largeFile = UploadedFile::fake()->image('large.jpg', 2000, 2000)->size(15000); // 15MB
        $largePath = $this->fileService->uploadFile($largeFile, 'large_test');
        $this->assertNull($largePath);
    }

    /** @test */
    public function it_generates_unique_filenames()
    {
        $file1 = UploadedFile::fake()->image('same_name.jpg');
        $file2 = UploadedFile::fake()->image('same_name.jpg');
        
        $path1 = $this->fileService->uploadFile($file1, 'unique_test_1');
        $path2 = $this->fileService->uploadFile($file2, 'unique_test_2');
        
        $this->assertNotEquals($path1, $path2);
        $this->assertNotNull($path1);
        $this->assertNotNull($path2);
    }

    /** @test */
    public function it_creates_settings_directory()
    {
        $file = UploadedFile::fake()->image('directory_test.jpg');
        $path = $this->fileService->uploadFile($file, 'directory_test');
        
        $this->assertStringStartsWith('settings/', $path);
        Storage::disk('public')->assertDirectoryExists('settings');
    }
}
