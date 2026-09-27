<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validate(array $data)
    {
        $request = new StoreContactRequest;

        return Validator::make($data, $request->rules());
    }

    public function test_valid_contact_data_is_accepted(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $validator = $this->validate([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '北海道旭川市',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です。',
            'tag_ids' => [$tag->id],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_invalid_tel_is_rejected(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $validator = $this->validate([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '090-1234-5678',
            'address' => '北海道旭川市',
            'building' => null,
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です。',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('tel'));
    }
}
