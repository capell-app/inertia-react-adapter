<?php

declare(strict_types=1);

namespace Capell\InertiaReactAdapter\Support;

use Capell\Frontend\Contracts\FrontendResourceContributor;
use Capell\Frontend\Data\Assets\FrontendResourceContributionData;
use Capell\Frontend\Data\Assets\FrontendResourceData;
use Capell\Frontend\Data\Assets\ViteResourceSourceData;
use Capell\Frontend\Data\FrontendResourceContextData;
use Capell\Inertia\Actions\ResolveInertiaAdapterKeyAction;
use Capell\InertiaReactAdapter\Providers\InertiaReactAdapterServiceProvider;

final class InertiaReactFrontendResourceContributor implements FrontendResourceContributor
{
    public function resources(FrontendResourceContextData $context): array
    {
        if (! $context->runtime->usesInertia || ResolveInertiaAdapterKeyAction::run() !== InertiaReactAdapterServiceProvider::ADAPTER_KEY) {
            return [];
        }

        return [new FrontendResourceContributionData(FrontendResourceData::moduleScript(
            handle: 'capell-app/inertia-react-adapter:application',
            package: InertiaReactAdapterServiceProvider::$packageName,
            source: new ViteResourceSourceData(InertiaReactAdapterServiceProvider::VITE_INPUT),
        ))];
    }
}
