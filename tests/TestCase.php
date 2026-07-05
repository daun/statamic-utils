<?php

namespace Tests;

use Daun\StatamicUtils\Modifiers;
use Daun\StatamicUtils\Tags;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Statamic\Providers\StatamicServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $viewPaths = $app['config']->get('view.paths');
        $viewPaths[] = __DIR__.'/__fixtures__/views/';

        $app['config']->set('view.paths', $viewPaths);
    }

    protected function getPackageProviders($app)
    {
        return [
            StatamicServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => 'Statamic\Statamic',
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerAddon();
    }

    /**
     * The add-on ships without a service provider; consumers are expected to
     * register the pieces they use. We register everything here so the Antlers
     * parser resolves the modifiers and tags during tests.
     */
    protected function registerAddon(): void
    {
        Modifiers\Asset::register();
        Modifiers\AssetMeta::register();
        Modifiers\Br2Nl::register();
        Modifiers\CountSafe::register();
        Modifiers\Except::register();
        Modifiers\Hostname::register();
        Modifiers\IsCurrent::register();
        Modifiers\Max::register();
        Modifiers\Min::register();
        Modifiers\Nl2Str::register();
        Modifiers\Orientation::register();
        Modifiers\P2Br::register();
        Modifiers\Push::register();
        Modifiers\QrCode::register();
        Modifiers\QueryAppend::register();
        Modifiers\Resolve::register();
        Modifiers\StandardRatio::register();
        Modifiers\ToFloat::register();
        Modifiers\ToInt::register();
        Modifiers\ToIterable::register();
        Modifiers\WrapWords::register();

        Tags\Capture::register();
        Tags\GetMountRoot::register();
        Tags\Icon::register();
        Tags\IfContent::register();
        Tags\Key::register();
        Tags\Random::register();
        Tags\Repeat::register();
    }
}
