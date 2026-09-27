<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_belongs_to_many_contacts(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $contact1 = Contact::create([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '北海道旭川市',
            'building' => null,
            'detail' => 'お問い合わせ1',
        ]);

        $contact2 = Contact::create([
            'category_id' => $category->id,
            'first_name' => '佐藤',
            'last_name' => '花子',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '北海道札幌市',
            'building' => null,
            'detail' => 'お問い合わせ2',
        ]);

        $tag->contacts()->attach([
            $contact1->id,
            $contact2->id,
        ]);

        $this->assertCount(2, $tag->fresh()->contacts);

        $this->assertTrue(
            $tag->fresh()->contacts->contains('id', $contact1->id)
        );

        $this->assertTrue(
            $tag->fresh()->contacts->contains('id', $contact2->id)
        );
    }
}
