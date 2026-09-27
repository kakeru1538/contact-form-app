<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_can_download_csv(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        Contact::create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '北海道旭川市',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => 'CSV出力テストです。',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export');

        $response->assertStatus(200);

        $response->assertHeader(
            'content-type',
            'text/csv; charset=UTF-8'
        );
    }

    public function test_csv_export_applies_filters(): void
    {
        $user = User::factory()->create();

        $category1 = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $category2 = Category::create([
            'content' => '商品トラブル',
        ]);

        Contact::create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '北海道旭川市',
            'building' => null,
            'category_id' => $category1->id,
            'detail' => 'CSV対象データ',
        ]);

        Contact::create([
            'first_name' => '佐藤',
            'last_name' => '花子',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '北海道札幌市',
            'building' => null,
            'category_id' => $category2->id,
            'detail' => 'CSV対象外データ',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(
                '/contacts/export?keyword=山田&gender=1&category_id='
                .$category1->id
            );

        $response->assertStatus(200);

        $content = $response->streamedContent();

        $this->assertStringContainsString('山田', $content);
        $this->assertStringContainsString('taro@example.com', $content);

        $this->assertStringNotContainsString('佐藤', $content);
        $this->assertStringNotContainsString('hanako@example.com', $content);
    }

    public function test_csv_export_is_ordered_by_latest_when_no_filters_are_given(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $oldContact = Contact::create([
            'first_name' => '古い',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'old@example.com',
            'tel' => '09011111111',
            'address' => '北海道旭川市',
            'building' => null,
            'category_id' => $category->id,
            'detail' => '古いお問い合わせ',
        ]);

        $oldContact->created_at = '2026-09-01 10:00:00';
        $oldContact->save();

        $newContact = Contact::create([
            'first_name' => '新しい',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'new@example.com',
            'tel' => '09022222222',
            'address' => '北海道旭川市',
            'building' => null,
            'category_id' => $category->id,
            'detail' => '新しいお問い合わせ',
        ]);

        $newContact->created_at = '2026-09-27 10:00:00';
        $newContact->save();

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export');

        $response->assertStatus(200);

        $content = $response->streamedContent();

        $newPosition = strpos($content, 'new@example.com');
        $oldPosition = strpos($content, 'old@example.com');

        $this->assertNotFalse($newPosition);
        $this->assertNotFalse($oldPosition);

        $this->assertLessThan(
            $oldPosition,
            $newPosition
        );
    }
}
