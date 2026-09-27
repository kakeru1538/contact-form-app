<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_contact_detail(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $contact = Contact::create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '北海道旭川市',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => '商品の配送について確認したいです。',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/contacts/'.$contact->id);

        $response->assertStatus(200);

        $response->assertViewIs('admin.show');

        $response->assertSee('山田');
        $response->assertSee('太郎');
        $response->assertSee('taro@example.com');
        $response->assertSee('商品のお届けについて');
        $response->assertSee('商品の配送について確認したいです。');
    }

    public function test_admin_can_delete_contact(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $contact = Contact::create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '北海道旭川市',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => '削除テストのお問い合わせです。',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete('/admin/contacts/'.$contact->id);

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }
}
