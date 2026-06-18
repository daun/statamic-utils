<?php

use Daun\StatamicUtils\ControlPanel\Translations;
use Statamic\Facades\Collection as Collections;
use Statamic\Facades\Taxonomy as Taxonomies;

test('ensureCreateButtonLabels passes when there is nothing to check', function () {
    Collections::shouldReceive('all')->andReturn(collect());
    Taxonomies::shouldReceive('all')->andReturn(collect());

    Translations::ensureCreateButtonLabels();

    expect(true)->toBeTrue();
});

test('ensureCreateButtonLabels throws when a collection create button label is missing', function () {
    $collection = Mockery::mock();
    $collection->shouldReceive('handle')->andReturn('blog');

    Collections::shouldReceive('all')->andReturn(collect([$collection]));
    Taxonomies::shouldReceive('all')->andReturn(collect());

    Translations::ensureCreateButtonLabels();
})->throws(Exception::class, 'Missing translation key: messages.blog_collection_create_entry');

test('ensureCreateButtonLabels throws when a taxonomy create button label is missing', function () {
    $taxonomy = Mockery::mock();
    $taxonomy->shouldReceive('handle')->andReturn('topics');

    Collections::shouldReceive('all')->andReturn(collect());
    Taxonomies::shouldReceive('all')->andReturn(collect([$taxonomy]));

    Translations::ensureCreateButtonLabels();
})->throws(Exception::class, 'Missing translation key: messages.topics_taxonomy_create_term');
