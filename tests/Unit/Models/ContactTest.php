<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_belongs_to_category(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '北海道旭川市',
            'building' => null,
            'detail' => 'お問い合わせ内容',
        ]);

        $this->assertInstanceOf(Category::class, $contact->category);
        $this->assertEquals($category->id, $contact->category->id);
    }

    public function test_contact_tags_can_be_synced(): void
    {
        $category = Category::create([
            'content' => 'テストカテゴリ',
        ]);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '北海道旭川市',
            'building' => null,
            'detail' => 'お問い合わせ内容',
        ]);

        $tag1 = Tag::create([
            'name' => '質問',
        ]);

        $tag2 = Tag::create([
            'name' => '要望',
        ]);

        $tag3 = Tag::create([
            'name' => '不具合報告',
        ]);

        // 「質問」「要望」を関連付ける
        $contact->tags()->sync([
            $tag1->id,
            $tag2->id,
        ]);

        $this->assertCount(2, $contact->fresh()->tags);

        // 「要望」「不具合報告」に変更する
        $contact->tags()->sync([
            $tag2->id,
            $tag3->id,
        ]);

        $tagIds = $contact->fresh()
            ->tags
            ->pluck('id')
            ->sort()
            ->values()
            ->toArray();

        $expectedTagIds = collect([
            $tag2->id,
            $tag3->id,
        ])
            ->sort()
            ->values()
            ->toArray();

        $this->assertEquals($expectedTagIds, $tagIds);

        // 古い「質問」タグとの関連が外れていることを確認
        $this->assertFalse(
            $contact->fresh()->tags->contains('id', $tag1->id)
        );
    }
}
