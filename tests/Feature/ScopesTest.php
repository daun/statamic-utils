<?php

use Daun\StatamicUtils\Scopes;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;

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

describe('origin scopes', function () {
    $contentPath = sys_get_temp_dir().'/statamic-utils-tests/origin-scopes';

    beforeEach(function () use ($contentPath) {
        app('files')->deleteDirectory($contentPath);
        Stache::store('collections')->directory($contentPath.'/collections');
        Stache::store('entries')->directory($contentPath.'/collections');
        Stache::clear();

        config()->set('statamic.system.multisite', true);

        Site::setSites([
            'en' => [
                'name' => 'English',
                'locale' => 'en_US',
                'url' => '/',
            ],
            'fr' => [
                'name' => 'French',
                'locale' => 'fr_FR',
                'url' => '/fr/',
            ],
        ]);

        Collection::make('origin_scope_test')
            ->title('Origin Scope Test')
            ->sites(['en', 'fr'])
            ->save();
    });

    afterEach(function () use ($contentPath) {
        Collection::find('origin_scope_test')?->delete();
        app('files')->deleteDirectory($contentPath);
        Site::setSites();
        config()->set('statamic.system.multisite', false);
    });

    test('origin scope returns origin entries', function () {
        $origin = Entry::make()
            ->collection('origin_scope_test')
            ->locale('en')
            ->slug('origin-entry')
            ->data(['title' => 'Origin Entry']);
        $origin->save();

        $localization = $origin->makeLocalization('fr');
        $localization->save();

        $query = Entry::query()->where('collection', 'origin_scope_test');
        (new Scopes\Origin)->apply($query, []);

        expect($query->get()->map->id()->all())->toBe([$origin->id()]);
    });

    test('localization scope returns localized entries', function () {
        $origin = Entry::make()
            ->collection('origin_scope_test')
            ->locale('en')
            ->slug('origin-entry')
            ->data(['title' => 'Origin Entry']);
        $origin->save();

        $localization = $origin->makeLocalization('fr');
        $localization->save();

        $query = Entry::query()->where('collection', 'origin_scope_test');
        (new Scopes\Localization)->apply($query, []);

        expect($query->get()->map->id()->all())->toBe([$localization->id()]);
    });
});
