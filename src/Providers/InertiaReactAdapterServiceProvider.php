<?php

declare(strict_types=1);

namespace Capell\InertiaReactAdapter\Providers;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Frontend\Contracts\FrontendResourceContributor;
use Capell\Frontend\Data\Assets\FrontendPackageDependencyData;
use Capell\Frontend\Enums\FrontendPackageDependencyType;
use Capell\Frontend\Support\Assets\FrontendPackageDependencyRegistry;
use Capell\Frontend\Support\Assets\FrontendViteInputRegistry;
use Capell\Inertia\Data\InertiaAdapterData;
use Capell\Inertia\Support\InertiaAdapterRegistry;
use Capell\InertiaReactAdapter\Support\InertiaReactFrontendResourceContributor;
use Override;
use Spatie\LaravelPackageTools\Package;

final class InertiaReactAdapterServiceProvider extends AbstractPackageServiceProvider
{
    public const string ADAPTER_KEY = 'react';

    public const string BUILD_PATH = 'vendor/capell/inertia-react';

    public const string ENTRYPOINT = 'resources/js/app.jsx';

    public const string VITE_INPUT = 'vendor/capell-app/inertia-react-adapter/resources/js/app.jsx';

    public static string $name = 'capell-inertia-react-adapter';

    public static string $packageName = 'capell-app/inertia-react-adapter';

    #[Override]
    public function configurePackage(Package $package): void
    {
        $package->name(self::$name);
    }

    #[Override]
    protected function bootInstalledRuntime(): void
    {
        $this->registerAdapter();
        $this->registerFrontendResources();
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerAdapter(): void
    {
        $configure = fn (InertiaAdapterRegistry $registry): InertiaAdapterRegistry => $registry->register($this->adapterData());

        $this->app->afterResolving(InertiaAdapterRegistry::class, $configure);

        if ($this->app->resolved(InertiaAdapterRegistry::class)) {
            $configure($this->app->make(InertiaAdapterRegistry::class));
        }
    }

    private function registerFrontendResources(): void
    {
        $registerDependencies = function (FrontendPackageDependencyRegistry $registry): void {
            foreach ($this->adapterData()->npmDependencies as $package => $version) {
                $registry->register(new FrontendPackageDependencyData(
                    name: $package,
                    versionConstraint: $version,
                    type: str_starts_with($package, '@vitejs/') ? FrontendPackageDependencyType::Development : FrontendPackageDependencyType::Runtime,
                    package: self::$packageName,
                ));
            }
        };

        $this->app->afterResolving(FrontendPackageDependencyRegistry::class, $registerDependencies);

        if ($this->app->resolved(FrontendPackageDependencyRegistry::class)) {
            $registerDependencies($this->app->make(FrontendPackageDependencyRegistry::class));
        }

        $registerInput = static function (FrontendViteInputRegistry $registry): void {
            $registry->register(self::VITE_INPUT, self::$packageName);
        };
        $this->app->afterResolving(FrontendViteInputRegistry::class, $registerInput);

        if ($this->app->resolved(FrontendViteInputRegistry::class)) {
            $registerInput($this->app->make(FrontendViteInputRegistry::class));
        }

        $this->app->tag(InertiaReactFrontendResourceContributor::class, FrontendResourceContributor::TAG);
    }

    private function adapterData(): InertiaAdapterData
    {
        return new InertiaAdapterData(
            key: self::ADAPTER_KEY,
            packageName: self::$packageName,
            npmDependencies: [
                '@inertiajs/react' => '^3.0.0',
                '@vitejs/plugin-react' => '^5.0.0',
                'react' => '^19.0.0',
                'react-dom' => '^19.0.0',
            ],
            buildPath: self::BUILD_PATH,
            entrypoint: self::ENTRYPOINT,
            components: [
                'page' => 'Capell/Page',
                'bookingRequest' => 'Capell/Bookings/Request',
            ],
        );
    }
}
