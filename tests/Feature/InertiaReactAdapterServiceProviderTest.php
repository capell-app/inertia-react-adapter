<?php

declare(strict_types=1);

use Capell\Frontend\Data\Assets\FrontendPackageDependencyData;
use Capell\Frontend\Data\Assets\ViteResourceSourceData;
use Capell\Frontend\Data\FrontendResourceContextData;
use Capell\Frontend\Data\FrontendRuntimeManifestData;
use Capell\Frontend\Enums\FrontendPackageDependencyType;
use Capell\Frontend\Enums\RenderingStrategyEnum;
use Capell\Frontend\Support\Assets\FrontendPackageDependencyRegistry;
use Capell\Frontend\Support\Assets\FrontendViteInputRegistry;
use Capell\Inertia\Support\InertiaAdapterRegistry;
use Capell\InertiaReactAdapter\Health\InertiaReactAdapterHealthCheck;
use Capell\InertiaReactAdapter\Providers\InertiaReactAdapterServiceProvider;
use Capell\InertiaReactAdapter\Support\InertiaReactFrontendResourceContributor;
use Capell\InertiaReactAdapter\Tests\InertiaReactAdapterTestCase;
use Illuminate\Support\Facades\File;

require_once __DIR__ . '/../../../inertia/src/Data/InertiaAdapterData.php';
require_once __DIR__ . '/../../../inertia/src/Support/InertiaAdapterRegistry.php';
require_once __DIR__ . '/../../../inertia/src/Providers/InertiaServiceProvider.php';
require_once __DIR__ . '/../../src/Health/InertiaReactAdapterHealthCheck.php';
require_once __DIR__ . '/../../src/Providers/InertiaReactAdapterServiceProvider.php';
require_once __DIR__ . '/../InertiaReactAdapterTestCase.php';

uses(InertiaReactAdapterTestCase::class);

it('registers the react adapter with the inertia bridge', function (): void {
    $adapter = resolve(InertiaAdapterRegistry::class)->get(InertiaReactAdapterServiceProvider::ADAPTER_KEY);

    throw_if($adapter === null, RuntimeException::class, 'React adapter was not registered.');

    expect($adapter)->not->toBeNull()
        ->and($adapter->key)->toBe('react')
        ->and($adapter->packageName)->toBe(InertiaReactAdapterServiceProvider::$packageName)
        ->and($adapter->buildPath)->toBe(InertiaReactAdapterServiceProvider::BUILD_PATH)
        ->and($adapter->entrypoint)->toBe(InertiaReactAdapterServiceProvider::ENTRYPOINT)
        ->and($adapter->npmDependencies)->toMatchArray([
            '@inertiajs/react' => '^3.0.0',
            '@vitejs/plugin-react' => '^5.0.0',
            'react' => '^19.0.0',
            'react-dom' => '^19.0.0',
        ])
        ->and($adapter->components)->toMatchArray([
            'bookingRequest' => 'Capell/Bookings/Request',
            'page' => 'Capell/Page',
        ]);
});

it('registers typed dependencies and the conditional React Vite entry', function (): void {
    $dependencies = collect(resolve(FrontendPackageDependencyRegistry::class)->all())
        ->mapWithKeys(static fn (FrontendPackageDependencyData $dependency): array => [$dependency->name => [$dependency->versionConstraint, $dependency->type]])
        ->all();

    expect($dependencies)->toBe([
        '@inertiajs/react' => ['^3.0.0', FrontendPackageDependencyType::Runtime],
        '@vitejs/plugin-react' => ['^5.0.0', FrontendPackageDependencyType::Development],
        'react' => ['^19.0.0', FrontendPackageDependencyType::Runtime],
        'react-dom' => ['^19.0.0', FrontendPackageDependencyType::Runtime],
    ])->and(resolve(FrontendViteInputRegistry::class)->all())
        ->toContain(InertiaReactAdapterServiceProvider::VITE_INPUT);

    config()->set('capell-inertia.adapter', 'react');
    $runtime = FrontendRuntimeManifestData::forRenderingStrategy(RenderingStrategyEnum::BladeOnly);
    $runtime->usesInertia = true;
    $resources = resolve(InertiaReactFrontendResourceContributor::class)->resources(new FrontendResourceContextData(null, null, null, null, null, $runtime));
    $source = $resources[0]->resource->source;

    throw_unless($source instanceof ViteResourceSourceData, RuntimeException::class, 'React adapter must contribute a Vite resource source.');

    expect($resources)->toHaveCount(1)
        ->and($source->entry)->toBe(InertiaReactAdapterServiceProvider::VITE_INPUT);
});

it('keeps declared react components aligned with the generic app component map', function (): void {
    $adapter = resolve(InertiaAdapterRegistry::class)->get(InertiaReactAdapterServiceProvider::ADAPTER_KEY);
    throw_if($adapter === null, RuntimeException::class, 'React adapter was not registered.');

    $packagePath = dirname(__DIR__, 2);
    $entrypoint = $packagePath . '/' . InertiaReactAdapterServiceProvider::ENTRYPOINT;
    $appSource = File::get($entrypoint);

    foreach ($adapter->components as $componentName) {
        $componentPath = dirname($entrypoint) . '/Pages/' . $componentName . '.jsx';

        expect(File::exists($componentPath))->toBeTrue()
            ->and($appSource)->toContain("'" . $componentName . "':");
    }
});

it('passes health when the react adapter has been registered', function (): void {
    expect(InertiaReactAdapterHealthCheck::compatibleCapellApiVersion())->toBe('^1.0')
        ->and((new InertiaReactAdapterHealthCheck)->passes())->toBeTrue();
});
