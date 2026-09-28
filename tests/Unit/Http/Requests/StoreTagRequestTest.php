<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTagRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validate(array $data)
    {
        $request = new StoreTagRequest;

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    public function test_tag_name_is_required(): void
    {
        $validator = $this->validate([
            'name' => '',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            'タグ名を入力してください',
            $validator->errors()->first('name')
        );
    }

    public function test_tag_name_must_not_exceed_50_characters(): void
    {
        $validator = $this->validate([
            'name' => str_repeat('あ', 51),
        ]);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            'タグ名は50文字以内で入力してください',
            $validator->errors()->first('name')
        );
    }

    public function test_duplicate_tag_name_is_rejected(): void
    {
        Tag::create([
            'name' => '質問',
        ]);

        $validator = $this->validate([
            'name' => '質問',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            'そのタグ名は既に使用されています',
            $validator->errors()->first('name')
        );
    }
}
