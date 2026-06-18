<?php

use Daun\StatamicUtils\Scopes;

/**
 * A tiny fake query builder that records the arguments passed to whereIn().
 */
function fakeExtensionQuery(): object
{
    return new class
    {
        public ?string $column = null;

        public array $values = [];

        public function whereIn($column, $values, $boolean = 'and')
        {
            $this->column = $column;
            $this->values = $values;

            return $this;
        }
    };
}

test('image scope filters to pixel and vector image extensions', function () {
    $query = fakeExtensionQuery();
    (new Scopes\Image)->apply($query, []);

    expect($query->column)->toBe('extension');
    expect($query->values)->toBe(['gif', 'jpg', 'jpeg', 'png', 'apng', 'webp', 'avif', 'svg']);
});

test('image pixel scope filters to raster image extensions only', function () {
    $query = fakeExtensionQuery();
    (new Scopes\ImagePixel)->apply($query, []);

    expect($query->values)->toBe(['gif', 'jpg', 'jpeg', 'png', 'apng', 'webp', 'avif']);
});

test('image vector scope filters to svg only', function () {
    $query = fakeExtensionQuery();
    (new Scopes\ImageVector)->apply($query, []);

    expect($query->values)->toBe(['svg']);
});

test('video scope filters to video extensions', function () {
    $query = fakeExtensionQuery();
    (new Scopes\Video)->apply($query, []);

    expect($query->values)->toBe(['h264', 'mp4', 'm4v', 'ogv', 'webm', 'mov']);
});

test('audio scope filters to audio extensions', function () {
    $query = fakeExtensionQuery();
    (new Scopes\Audio)->apply($query, []);

    expect($query->values)->toBe(['aac', 'aiff', 'flac', 'm4a', 'mp3', 'ogg', 'wav']);
});

test('image or video scope filters to both image and video extensions', function () {
    $query = fakeExtensionQuery();
    (new Scopes\ImageOrVideo)->apply($query, []);

    expect($query->values)->toBe([
        'gif', 'jpg', 'jpeg', 'png', 'apng', 'webp', 'avif', 'svg',
        'h264', 'mp4', 'm4v', 'ogv', 'webm', 'mov',
    ]);
});

test('published scope uses whereStatus when available', function () {
    $query = new class
    {
        public ?string $status = null;

        public function whereStatus($status)
        {
            $this->status = $status;

            return $this;
        }
    };

    (new Scopes\Published)->apply($query, []);

    expect($query->status)->toBe('published');
});

test('published scope falls back to a where clause when whereStatus is missing', function () {
    $query = new class
    {
        public array $where = [];

        public function where($column, $value)
        {
            $this->where = [$column, $value];

            return $this;
        }
    };

    (new Scopes\Published)->apply($query, []);

    expect($query->where)->toBe(['status', 'published']);
});
