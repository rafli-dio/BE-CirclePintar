<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$teacher = App\Models\User::where('role', 'teacher')->first();
$category = App\Models\Category::create(['name'=>'Category 1', 'slug'=>'cat-1']);
$course = App\Models\Course::create(['user_id'=>$teacher->id, 'category_id'=>$category->id, 'title'=>'Course 1', 'description'=>'Desc']);
$module = App\Models\Module::create(['course_id'=>$course->id, 'title'=>'Module 1']);
$material = App\Models\Material::create([
    'module_id' => $module->id,
    'title' => 'Test Material',
    'type' => 'pdf',
    'content_url' => 'dummy.pdf'
]);
echo "Created Material ID: " . $material->id . "\n";
