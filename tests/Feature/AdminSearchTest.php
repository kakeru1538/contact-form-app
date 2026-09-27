<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_contacts(): void
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
            'detail' => 'テストお問い合わせ1',
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
            'detail' => 'テストお問い合わせ2',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin?keyword=山田&gender=1&category_id='.$category1->id);

        $response->assertStatus(200);

        $response->assertSee('山田');
        $response->assertDontSee('佐藤');
    }

    public function test_admin_can_filter_contacts_by_date(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $oldContact = Contact::create([
            'first_name' => '過去',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'old@example.com',
            'tel' => '09011111111',
            'address' => '北海道旭川市',
            'building' => null,
            'category_id' => $category->id,
            'detail' => '過去のお問い合わせ',
        ]);

        $oldContact->created_at = '2026-09-01 10:00:00';
        $oldContact->save();

        $todayContact = Contact::create([
            'first_name' => '今日',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'today@example.com',
            'tel' => '09022222222',
            'address' => '北海道旭川市',
            'building' => null,
            'category_id' => $category->id,
            'detail' => '今日のお問い合わせ',
        ]);

        $todayContact->created_at = '2026-09-27 10:00:00';
        $todayContact->save();

        $response = $this
            ->actingAs($user)
            ->get('/admin?date=2026-09-27');

        $response->assertStatus(200);
        $response->assertSee('今日');
        $response->assertDontSee('過去');
    }

    public function test_admin_contacts_are_paginated_by_seven(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        for ($i = 1; $i <= 8; $i++) {
            Contact::create([
                'first_name' => 'テスト'.$i,
                'last_name' => '太郎',
                'gender' => 1,
                'email' => 'test'.$i.'@example.com',
                'tel' => '09012345678',
                'address' => '北海道旭川市',
                'building' => null,
                'category_id' => $category->id,
                'detail' => 'お問い合わせ'.$i,
            ]);
        }

        $response = $this
            ->actingAs($user)
            ->get('/admin');

        $response->assertStatus(200);

        $response->assertViewHas('contacts', function ($contacts) {
            return $contacts->perPage() === 7
                && $contacts->total() === 8
                && $contacts->count() === 7;
        });
    }
}
