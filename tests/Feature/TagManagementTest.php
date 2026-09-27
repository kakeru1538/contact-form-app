<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_manage_tags(): void
    {
        $user = User::factory()->create();

        // タグ作成
        $response = $this
            ->actingAs($user)
            ->post('/admin/tags', [
                'name' => '新しいタグ',
            ]);

        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('tags', [
            'name' => '新しいタグ',
        ]);

        $tag = Tag::where('name', '新しいタグ')->firstOrFail();

        // 編集画面表示
        $response = $this
            ->actingAs($user)
            ->get('/admin/tags/'.$tag->id.'/edit');

        $response->assertStatus(200);

        // タグ更新
        $response = $this
            ->actingAs($user)
            ->put('/admin/tags/'.$tag->id, [
                'name' => '更新したタグ',
            ]);

        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '更新したタグ',
        ]);

        // タグ削除
        $response = $this
            ->actingAs($user)
            ->delete('/admin/tags/'.$tag->id);

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    public function test_guest_cannot_manage_tags(): void
    {
        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        // 編集画面
        $response = $this->get('/admin/tags/'.$tag->id.'/edit');
        $response->assertRedirect('/login');

        // 作成
        $response = $this->post('/admin/tags', [
            'name' => '新しいタグ',
        ]);
        $response->assertRedirect('/login');

        // 更新
        $response = $this->put('/admin/tags/'.$tag->id, [
            'name' => '更新したタグ',
        ]);
        $response->assertRedirect('/login');

        // 削除
        $response = $this->delete('/admin/tags/'.$tag->id);
        $response->assertRedirect('/login');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'テストタグ',
        ]);

        $this->assertDatabaseMissing('tags', [
            'name' => '新しいタグ',
        ]);
    }
}
