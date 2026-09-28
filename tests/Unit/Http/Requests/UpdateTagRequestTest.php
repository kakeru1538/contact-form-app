<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateTagRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validate(Tag $tag, array $data)
    {
        $request = new UpdateTagRequest;

        $request->setRouteResolver(function () use ($tag) {
            return new class($tag)
            {
                public function __construct(private Tag $tag) {}

                public function parameter($key, $default = null)
                {
                    return $key === 'tag' ? $this->tag : $default;
                }
            };
        });

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    public function test_tag_can_keep_its_own_name_when_updating(): void
    {
        $tag = Tag::create([
            'name' => '質問',
        ]);

        $validator = $this->validate($tag, [
            'name' => '質問',
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_tag_cannot_be_changed_to_another_existing_tag_name(): void
    {
        $tag = Tag::create([
            'name' => '質問',
        ]);

        Tag::create([
            'name' => '要望',
        ]);

        $validator = $this->validate($tag, [
            'name' => '要望',
        ]);

        $this->assertTrue($validator->fails());

        $this->assertSame(
            'そのタグ名は既に使用されています',
            $validator->errors()->first('name')
        );
    }
}
