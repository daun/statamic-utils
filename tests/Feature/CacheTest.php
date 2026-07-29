<?php

use Daun\StatamicUtils\Cache\QueryParams;

test('query params can be ignored by category', function () {
    expect(QueryParams::tracking())
        ->toContain('gclid', 'li_fat_id', 'mtm_campaign', 'ScCid', 'ttclid')
        ->not->toContain('_token', 'age-verified')
        ->and(QueryParams::functional())
        ->toBe(['_token', 'age-verified', 'ao_noptimize', 'cn-reloaded']);
});

test('all query params remain available through the combined list', function () {
    expect(QueryParams::toIgnore())
        ->toBe([
            ...QueryParams::tracking(),
            ...QueryParams::functional(),
        ])
        ->each->toBeString()
        ->and(QueryParams::toIgnore())
        ->toHaveCount(count(array_unique(QueryParams::toIgnore())));
});
