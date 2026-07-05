<?php

use Daun\StatamicUtils\Dictionaries\Collections;
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
