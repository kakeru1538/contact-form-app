<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contacts_api_returns_filtered_paginated_json(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        for ($i = 1; $i <= 8; $i++) {
            Contact::create([
                'first_name' => '山田',
                'last_name' => '太郎'.$i,
                'gender' => 1,
                'email' => 'taro'.$i.'@example.com',
                'tel' => '09012345678',
                'address' => '北海道旭川市',
                'building' => null,
                'category_id' => $category->id,
                'detail' => 'お問い合わせ'.$i,
            ]);
        }

        Contact::create([
            'first_name' => '佐藤',
            'last_name' => '花子',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '北海道札幌市',
            'building' => null,
            'category_id' => $category->id,
            'detail' => '別のお問い合わせ',
        ]);

        $response = $this->getJson(
            '/api/v1/contacts?keyword=山田&gender=1&per_page=5&page=1'
        );

        $response->assertStatus(200);

        $response->assertJsonCount(5, 'data');

        $response->assertJsonPath('meta.per_page', 5);
        $response->assertJsonPath('meta.total', 8);

        $response->assertJsonMissing([
            'email' => 'hanako@example.com',
        ]);
    }

    public function test_contacts_api_returns_422_when_search_parameters_are_invalid(): void
    {
        $response = $this->getJson(
            '/api/v1/contacts?gender=4&per_page=101&page=0'
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'gender',
            'per_page',
            'page',
        ]);
    }

    public function test_contact_detail_api_returns_contact_json(): void
    {
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
            'detail' => 'お問い合わせ内容です。',
        ]);

        $response = $this->getJson(
            '/api/v1/contacts/'.$contact->id
        );

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'id' => $contact->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'taro@example.com',
        ]);
    }

    public function test_contact_detail_api_returns_404_when_contact_does_not_exist(): void
    {
        $response = $this->getJson('/api/v1/contacts/9999');

        $response->assertStatus(404);

        $response->assertJson([
            'error' => 'お問い合わせが見つかりませんでした。',
        ]);
    }

    public function test_contact_can_be_created_via_api(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '北海道旭川市',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => 'APIからのお問い合わせです。',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('contacts', [
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'taro@example.com',
            'category_id' => $category->id,
        ]);
    }

    public function test_contact_create_api_returns_422_when_validation_fails(): void
    {
        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '',
            'last_name' => '',
            'gender' => 4,
            'email' => 'invalid-email',
            'tel' => '090-1234-5678',
            'address' => '',
            'category_id' => 9999,
            'detail' => '',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);

        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_contact_can_be_updated_via_api(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $contact = Contact::create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'old@example.com',
            'tel' => '09012345678',
            'address' => '北海道旭川市',
            'building' => null,
            'category_id' => $category->id,
            'detail' => '更新前のお問い合わせです。',
        ]);

        $response = $this->putJson(
            '/api/v1/contacts/'.$contact->id,
            [
                'first_name' => '山田',
                'last_name' => '太郎',
                'gender' => 1,
                'email' => 'new@example.com',
                'tel' => '09012345678',
                'address' => '北海道旭川市',
                'building' => '更新後ビル101',
                'category_id' => $category->id,
                'detail' => '更新後のお問い合わせです。',
            ]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'email' => 'new@example.com',
            'building' => '更新後ビル101',
            'detail' => '更新後のお問い合わせです。',
        ]);

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
            'email' => 'old@example.com',
        ]);
    }

    public function test_contact_update_api_returns_404_when_contact_does_not_exist(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $response = $this->putJson('/api/v1/contacts/9999', [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '北海道旭川市',
            'building' => null,
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です。',
        ]);

        $response->assertStatus(404);

        $response->assertJson([
            'error' => 'お問い合わせが見つかりませんでした。',
        ]);
    }

    public function test_contact_update_api_returns_422_when_validation_fails(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $contact = Contact::create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'before@example.com',
            'tel' => '09012345678',
            'address' => '北海道旭川市',
            'building' => null,
            'category_id' => $category->id,
            'detail' => '更新前のお問い合わせです。',
        ]);

        $response = $this->putJson(
            '/api/v1/contacts/'.$contact->id,
            [
                'first_name' => '',
                'last_name' => '',
                'gender' => 4,
                'email' => 'invalid-email',
                'tel' => '090-1234-5678',
                'address' => '',
                'category_id' => 9999,
                'detail' => '',
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'email' => 'before@example.com',
            'detail' => '更新前のお問い合わせです。',
        ]);
    }

    public function test_contact_can_be_deleted_via_api(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $contact = Contact::create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'delete@example.com',
            'tel' => '09012345678',
            'address' => '北海道旭川市',
            'building' => null,
            'category_id' => $category->id,
            'detail' => '削除するお問い合わせです。',
        ]);

        $response = $this->deleteJson(
            '/api/v1/contacts/'.$contact->id
        );

        $response->assertStatus(204);

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_contact_delete_api_returns_404_when_contact_does_not_exist(): void
    {
        $response = $this->deleteJson('/api/v1/contacts/9999');

        $response->assertStatus(404);

        $response->assertJson([
            'error' => 'お問い合わせが見つかりませんでした。',
        ]);
    }
}
