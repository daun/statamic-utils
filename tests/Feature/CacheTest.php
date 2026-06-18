<?php

use Daun\StatamicUtils\Cache\QueryParams;

test('query params to ignore is a flat list of strings', function () {
    $params = QueryParams::toIgnore();

    expect($params)->toBeArray();
    expect($params)->each->toBeString();
});

test('query params to ignore includes common marketing parameters', function () {
    $params = QueryParams::toIgnore();

    expect($params)->toContain('utm_source');
    expect($params)->toContain('utm_medium');
    expect($params)->toContain('utm_campaign');
    expect($params)->toContain('gclid');
    expect($params)->toContain('fbclid');
    expect($params)->toContain('mc_cid');
});

test('query params to ignore contains no duplicates', function () {
    $params = QueryParams::toIgnore();

    expect($params)->toBe(array_values(array_unique($params)));
});
