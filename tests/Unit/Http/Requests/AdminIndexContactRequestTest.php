<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AdminIndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validate(array $data)
    {
        $request = new IndexContactRequest;

        return Validator::make($data, $request->rules());
    }

    public function test_keyword_is_valid(): void
    {
        $validator = $this->validate([
            'keyword' => '山田',
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_gender_is_valid(): void
    {
        $validator = $this->validate([
            'gender' => 1,
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_category_is_valid(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $validator = $this->validate([
            'category_id' => $category->id,
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_date_is_valid(): void
    {
        $validator = $this->validate([
            'date' => '2026-09-27',
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_invalid_gender_is_rejected(): void
    {
        $validator = $this->validate([
            'gender' => 4,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('gender'));
    }
}
