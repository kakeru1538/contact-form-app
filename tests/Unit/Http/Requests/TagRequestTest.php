<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\TagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TagRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validate(array $data)
    {
        $request = new TagRequest;

        return Validator::make($data, $request->rules());
    }

    public function test_tag_name_is_required(): void
    {
        $validator = $this->validate([
            'name' => '',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }

    public function test_tag_name_must_not_exceed_50_characters(): void
    {
        $validator = $this->validate([
            'name' => str_repeat('あ', 51),
        ]);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
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
        $this->assertTrue($validator->errors()->has('name'));
    }

    public function test_tag_can_keep_its_own_name_when_updating(): void
    {
        $tag = Tag::create([
            'name' => '質問',
        ]);

        $request = new TagRequest;

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

        $validator = Validator::make([
            'name' => '質問',
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_tag_cannot_be_changed_to_another_existing_tag_name(): void
    {
        $tag = Tag::create([
            'name' => '質問',
        ]);

        Tag::create([
            'name' => '要望',
        ]);

        $request = new TagRequest;

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

        $validator = Validator::make([
            'name' => '要望',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }
}
