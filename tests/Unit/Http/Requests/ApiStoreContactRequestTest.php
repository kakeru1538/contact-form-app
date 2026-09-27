<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ApiStoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validate(array $data)
    {
        $request = new StoreContactRequest;

        return Validator::make(
            $data,
            $request->rules()
        );
    }

    public function test_valid_contact_data_with_tags_is_accepted(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $tag1 = Tag::create([
            'name' => '質問',
        ]);

        $tag2 = Tag::create([
            'name' => '要望',
        ]);

        $validator = $this->validate([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '北海道旭川市',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です。',
            'tag_ids' => [
                $tag1->id,
                $tag2->id,
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_invalid_contact_data_is_rejected(): void
    {
        $validator = $this->validate([
            'first_name' => '',
            'last_name' => '',
            'gender' => 4,
            'email' => 'invalid-email',
            'tel' => '090-1234-5678',
            'address' => '',
            'category_id' => 9999,
            'detail' => str_repeat('あ', 121),
            'tag_ids' => [9999],
        ]);

        $this->assertTrue($validator->fails());

        $this->assertTrue($validator->errors()->has('first_name'));
        $this->assertTrue($validator->errors()->has('last_name'));
        $this->assertTrue($validator->errors()->has('gender'));
        $this->assertTrue($validator->errors()->has('email'));
        $this->assertTrue($validator->errors()->has('tel'));
        $this->assertTrue($validator->errors()->has('address'));
        $this->assertTrue($validator->errors()->has('category_id'));
        $this->assertTrue($validator->errors()->has('detail'));
        $this->assertTrue($validator->errors()->has('tag_ids.0'));
    }
}
