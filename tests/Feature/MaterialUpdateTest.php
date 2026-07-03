<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Material;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaterialUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_material_using_post_spoofing()
    {
        Storage::fake('public');

        $teacher = User::create([
            'name' => 'Teacher',
            'email' => 'teacher@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher'
        ]);
        $category = \App\Models\Category::create(['name' => 'Category', 'slug' => 'category']);
        $course = Course::create(['user_id' => $teacher->id, 'title' => 'Test Course', 'description' => 'Test', 'category_id' => $category->id]);
        $module = Module::create(['course_id' => $course->id, 'title' => 'Test Module']);
        $material = Material::create([
            'module_id' => $module->id,
            'title' => 'Old Title',
            'type' => 'pdf',
            'disk' => 'local',
            'content_url' => 'old.pdf'
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($teacher)->postJson("/api/materials/{$material->id}", [
            '_method' => 'PUT',
            'title' => 'New Title',
            'file' => $file,
        ]);

        if ($response->getStatusCode() !== 200) {
            dump($response->json());
        }

        $response->assertStatus(200);
        $this->assertEquals('New Title', $material->fresh()->title);
    }
}
