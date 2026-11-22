<?php

declare(strict_types=1);

namespace Capell\InertiaReactAdapter\Support;

use Capell\Core\Contracts\Rendering\FrontendResourceContributor;
use Capell\Core\Data\Rendering\Assets\FrontendResourceContributionData;
use Capell\Core\Data\Rendering\Assets\FrontendResourceData;
use Capell\Core\Data\Rendering\FrontendResourceContextData;
use Capell\Frontend\Data\Assets\ViteResourceSourceData;
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
