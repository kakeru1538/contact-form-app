<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactConfirmTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_confirm_page_is_displayed(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $response = $this->post('/contacts/confirm', [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '北海道旭川市',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です。',
            'tag_ids' => [$tag->id],
        ]);

        $response->assertStatus(200);

        $response->assertViewIs('contact.confirm');

        $response->assertSee('山田');
        $response->assertSee('太郎');
        $response->assertSee('taro@example.com');
        $response->assertSee('商品のお届けについて');
    }

    public function test_contact_confirm_redirects_when_validation_fails(): void
    {
        $response = $this
            ->from('/')
            ->post('/contacts/confirm', [
                'first_name' => '',
                'last_name' => '',
                'gender' => '',
                'email' => '',
                'tel' => '',
                'address' => '',
                'category_id' => '',
                'detail' => '',
            ]);

        $response->assertRedirect('/');

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);
    }
}
