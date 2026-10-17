<?php

declare(strict_types=1);

use Capell\Core\Actions\InstallPackageAction;
use Capell\Core\Actions\RuntimeRefresh\RefreshInstalledPackageRuntimeAction;
use Capell\Core\Facades\CapellCore;
use Capell\Frontend\Data\Assets\FrontendPackageDependencyData;
use Capell\Frontend\Enums\FrontendPackageDependencyType;
use Capell\Frontend\Support\Assets\FrontendPackageDependencyRegistry;
use Capell\Frontend\Support\Assets\FrontendViteInputRegistry;
use Capell\Inertia\Support\InertiaAdapterRegistry;
use Capell\InertiaReactAdapter\Providers\InertiaReactAdapterServiceProvider;
use Capell\Tests\Support\InstalledRuntimeParity;
use Capell\Tests\Support\InstalledRuntimeState;
use Capell\Tests\Support\InstalledRuntimeTestCase;

it('matches fresh installed boot after in-process installation and repeated refresh', function (): void {
    InstalledRuntimeParity::assertPackage('inertia-react-adapter');
});

it('contributes once to registries resolved before installation and to later resolutions', function (bool $transient): void {
    InstalledRuntimeState::run(static function () use ($transient): void {
        $app = InstalledRuntimeTestCase::boot(InstalledRuntimeParity::manifest('inertia-react-adapter'), false, sourceRoot: dirname(__DIR__, 3));
        $resolutions = new class
        {
            /** @var list<FrontendPackageDependencyRegistry> */
            private array $registries = [];

            public function record(FrontendPackageDependencyRegistry $registry): void
            {
                $this->registries[] = $registry;
            }

            public function clear(): void
            {
                $this->registries = [];
            }

            /** @return list<FrontendPackageDependencyRegistry> */
            public function all(): array
            {
                return $this->registries;
            }
        };
        if ($transient) {
            $app->bind(FrontendPackageDependencyRegistry::class, static function () use ($resolutions): FrontendPackageDependencyRegistry {
                $registry = new FrontendPackageDependencyRegistry;
                $resolutions->record($registry);

                return $registry;
            });
        }

        $dependencies = $app->make(FrontendPackageDependencyRegistry::class);
        $adapters = $app->make(InertiaAdapterRegistry::class);
        $inputs = $app->make(FrontendViteInputRegistry::class);
        $ownedDependencies = static fn (FrontendPackageDependencyRegistry $registry): array => array_values(array_map(
            static fn (FrontendPackageDependencyData $dependency): string => $dependency->name,
            array_filter($registry->all(), static fn (FrontendPackageDependencyData $dependency): bool => $dependency->package === InertiaReactAdapterServiceProvider::$packageName),
        ));

        expect($ownedDependencies($dependencies))->toBe([])
            ->and($adapters->get(InertiaReactAdapterServiceProvider::ADAPTER_KEY))->toBeNull()
            ->and($inputs->all())->not->toContain(InertiaReactAdapterServiceProvider::VITE_INPUT);

        $resolutions->clear();
        InstalledRuntimeTestCase::prepareLateInstallMigrations($app);
        $package = CapellCore::getPackage(InertiaReactAdapterServiceProvider::$packageName);
        InstallPackageAction::run($package);

        foreach ([1, 2, 3] as $phase) {
            expect($ownedDependencies($app->make(FrontendPackageDependencyRegistry::class)))->toBe(['@inertiajs/react', '@vitejs/plugin-react', 'react', 'react-dom'])
                ->and($adapters->get(InertiaReactAdapterServiceProvider::ADAPTER_KEY)?->packageName)->toBe(InertiaReactAdapterServiceProvider::$packageName)
                ->and(array_values(array_filter($inputs->all(), static fn (string $input): bool => $input === InertiaReactAdapterServiceProvider::VITE_INPUT)))->toBe([InertiaReactAdapterServiceProvider::VITE_INPUT]);

            if (! $transient) {
                expect($ownedDependencies($dependencies))->toBe(['@inertiajs/react', '@vitejs/plugin-react', 'react', 'react-dom']);
            }

            foreach ($resolutions->all() as $registry) {
                expect($ownedDependencies($registry))->toBe(['@inertiajs/react', '@vitejs/plugin-react', 'react', 'react-dom']);
            }

            RefreshInstalledPackageRuntimeAction::run($package);
        }
    });
})->with(['singleton' => false, 'transient' => true]);

it('preserves differing dependency registrations through installation and refresh', function (string $constraint, FrontendPackageDependencyType $type): void {
    InstalledRuntimeState::run(static function () use ($constraint, $type): void {
        $app = InstalledRuntimeTestCase::boot(InstalledRuntimeParity::manifest('inertia-react-adapter'), false, sourceRoot: dirname(__DIR__, 3));
        $dependencies = $app->make(FrontendPackageDependencyRegistry::class);
        $existing = new FrontendPackageDependencyData(
            name: 'react',
            versionConstraint: $constraint,
            type: $type,
            package: InertiaReactAdapterServiceProvider::$packageName,
        );
        $dependencies->register($existing);

        InstalledRuntimeTestCase::prepareLateInstallMigrations($app);
        $package = CapellCore::getPackage(InertiaReactAdapterServiceProvider::$packageName);
        InstallPackageAction::run($package);

        foreach ([1, 2, 3] as $phase) {
            $requirements = array_values(array_filter(
                $dependencies->all(),
                static fn (FrontendPackageDependencyData $dependency): bool => $dependency->package === InertiaReactAdapterServiceProvider::$packageName && $dependency->name === 'react',
            ));

            expect($requirements)->toHaveCount(2)
                ->and($requirements[0])->toBe($existing)
                ->and($requirements[1]->versionConstraint)->toBe('^19.0.0')
                ->and($requirements[1]->type)->toBe(FrontendPackageDependencyType::Runtime);

            RefreshInstalledPackageRuntimeAction::run($package);
        }
    });
})->with([
    'changed constraint' => ['^18.0.0', FrontendPackageDependencyType::Runtime],
    'changed type' => ['^19.0.0', FrontendPackageDependencyType::Development],
]);
