<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validate(array $data)
    {
        $request = new IndexContactRequest;

        return Validator::make(
            $data,
            $request->rules()
        );
    }

    public function test_valid_search_parameters_are_accepted(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $validator = $this->validate([
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-09-27',
            'page' => 1,
            'per_page' => 20,
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

    public function test_invalid_category_is_rejected(): void
    {
        $validator = $this->validate([
            'category_id' => 9999,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('category_id'));
    }

    public function test_invalid_page_is_rejected(): void
    {
        $validator = $this->validate([
            'page' => 0,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('page'));
    }

    public function test_invalid_per_page_is_rejected(): void
    {
        $validator = $this->validate([
            'per_page' => 101,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('per_page'));
    }
}
