<?php

/*
|--------------------------------------------------------------------------
| Capture
|--------------------------------------------------------------------------
*/

test('capture assigns block output to a named variable', function () {
    $template = <<<'EOT'
{{ capture:greeting }}Hello{{ /capture:greeting }}[{{ greeting }}]
EOT;

    expect(antlers($template))->toBe('[Hello]');
});

test('capture does not output the captured block in place', function () {
    $template = <<<'EOT'
before{{ capture:greeting }}Hello{{ /capture:greeting }}after
EOT;

    expect(antlers($template))->toBe('beforeafter');
});

test('capture trims output when the trim parameter is set', function () {
    $trimmed = <<<'EOT'
{{ capture:greeting trim="true" }}   Hello   {{ /capture:greeting }}[{{ greeting }}]
EOT;
    $untrimmed = <<<'EOT'
{{ capture:greeting }}   Hello   {{ /capture:greeting }}[{{ greeting }}]
EOT;

    expect(antlers($trimmed))->toBe('[Hello]');
    expect(antlers($untrimmed))->toBe('[   Hello   ]');
});

test('capture captures when the when condition is true', function () {
    // Note: the capture tag writes to the global Cascade, so each assertion
    // uses a distinct variable name to avoid state bleeding between renders.
    $template = <<<'EOT'
{{ capture:when_true :when="show" }}Hello{{ /capture:when_true }}[{{ when_true }}]
EOT;

    expect(antlers($template, ['show' => true]))->toBe('[Hello]');
});

test('capture skips capturing when the when condition is false', function () {
    $template = <<<'EOT'
{{ capture:when_false :when="show" }}Hello{{ /capture:when_false }}[{{ when_false }}]
EOT;

    expect(antlers($template, ['show' => false]))->toBe('[]');
});

/*
|--------------------------------------------------------------------------
| Icon
|--------------------------------------------------------------------------
*/

test('icon renders an svg referencing the sprite symbol', function () {
    $output = antlers('{{ icon:search }}');

    expect($output)->toContain('<svg');
    expect($output)->toContain('class="icon icon-search"');
    expect($output)->toContain('xlink:href="#icon-search"');
    expect($output)->toContain('aria-hidden="true"');
});

test('icon uses the xMinYMid aspect ratio by default', function () {
    expect(antlers('{{ icon:search }}'))->toContain('preserveAspectRatio="xMinYMid"');
});

test('icon accepts a custom aspect ratio', function () {
    expect(antlers('{{ icon:search ratio="none" }}'))->toContain('preserveAspectRatio="none"');
});

/*
|--------------------------------------------------------------------------
| IfContent
|--------------------------------------------------------------------------
*/

test('if_content renders content that has text', function () {
    $template = <<<'EOT'
{{ if_content }}<p>Hello</p>{{ /if_content }}
EOT;

    expect(antlers($template))->toBe('<p>Hello</p>');
});

test('if_content suppresses content that is effectively empty', function () {
    $template = <<<'EOT'
{{ if_content }}<p></p>{{ /if_content }}
EOT;

    expect(antlers($template))->toBe('');
});

test('if_content suppresses whitespace-only content', function () {
    $template = <<<'EOT'
{{ if_content }}     {{ /if_content }}
EOT;

    expect(trim(antlers($template)))->toBe('');
});

test('if_content renders media-only content even without text', function () {
    $template = <<<'EOT'
{{ if_content }}<img src="cat.jpg">{{ /if_content }}
EOT;

    expect(antlers($template))->toContain('<img src="cat.jpg">');
});

/*
|--------------------------------------------------------------------------
| Repeat
|--------------------------------------------------------------------------
*/

test('repeat outputs the content the given number of times', function () {
    $template = <<<'EOT'
{{ repeat times="3" }}x{{ /repeat }}
EOT;

    expect(antlers($template))->toBe('xxx');
});

test('repeat outputs nothing when times is zero', function () {
    $template = <<<'EOT'
{{ repeat times="0" }}x{{ /repeat }}
EOT;

    expect(antlers($template))->toBe('');
});

test('repeat clamps negative times to zero', function () {
    $template = <<<'EOT'
{{ repeat times="-5" }}x{{ /repeat }}
EOT;

    expect(antlers($template))->toBe('');
});

/*
|--------------------------------------------------------------------------
| Get Mount Root
|--------------------------------------------------------------------------
*/

test('get_mount_root yields nothing when no root entry matches the current url', function () {
    expect(antlers('{{ get_mount_root }}'))->toBe('');
});

test('get_mount_root accepts an explicit url via the of parameter', function () {
    expect(antlers('{{ get_mount_root of="/no-such-page" }}'))->toBe('');
});

/*
|--------------------------------------------------------------------------
| Key
|--------------------------------------------------------------------------
*/

test('key injects a stable key attribute into the first element', function () {
    $template = <<<'EOT'
{{ key:tag }}<div class="card">Hello</div>{{ /key:tag }}
EOT;

    $output = antlers($template);

    expect($output)->toStartWith('<div key=');
    expect($output)->toContain('data-skip-morph-if-keys-equal');
    expect($output)->toContain('>Hello</div>');
});

test('key derives the same key for identical content', function () {
    $template = <<<'EOT'
{{ key:tag }}<div class="card">Hello</div>{{ /key:tag }}
EOT;

    expect(antlers($template))->toBe(antlers($template));
});

test('key throws when used as a single tag', function () {
    expect(fn () => antlers('{{ key:tag }}'))->toThrow(Exception::class);
});

/*
|--------------------------------------------------------------------------
| Random
|--------------------------------------------------------------------------
*/

test('random outputs a 32 character hexadecimal hash', function () {
    expect(antlers('{{ random }}'))->toMatch('/^[a-f0-9]{32}$/');
});

test('random produces a different value on each render', function () {
    expect(antlers('{{ random }}'))->not->toBe(antlers('{{ random }}'));
});

test('random int respects an inclusive min and max', function () {
    expect(antlers('{{ random:int min="7" max="7" }}'))->toBe('7');
});

test('random int stays within the given bounds', function () {
    $value = (int) antlers('{{ random:int min="1" max="10" }}');

    expect($value)->toBeGreaterThanOrEqual(1);
    expect($value)->toBeLessThanOrEqual(10);
});
