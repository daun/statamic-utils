<?php

use Daun\StatamicUtils\Dictionaries\Collections;
use Daun\StatamicUtils\Dictionaries\Locales;
use Statamic\Facades\Collection;

/*
|--------------------------------------------------------------------------
| Collections
|--------------------------------------------------------------------------
*/

test('collections dictionary maps collection handles to titles', function () {
    Collection::make('blog')->title('Blog')->save();
    Collection::make('pages')->title('Pages')->save();

    $options = (new Collections)->options();

    expect($options)->toMatchArray([
        'blog' => 'Blog',
        'pages' => 'Pages',
    ]);
});

/*
|--------------------------------------------------------------------------
| Locales
|--------------------------------------------------------------------------
*/

test('locales dictionary maps short locales to site names', function () {
    $options = (new Locales)->options();

    expect($options)->toBe(['en' => 'Laravel']);
});
