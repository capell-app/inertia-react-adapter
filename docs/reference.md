# Capell Inertia React Adapter reference

<!-- prettier-ignore-start -->

Developer reference for `capell-app/inertia-react-adapter`. Start with the [README](../README.md) for what the extension does and how to install it.

## Package Details

Capell Inertia React Adapter is an **Available**, **No schema impact** Capell plugin in the **Capell Frontend** product group. It ships as `capell-app/inertia-react-adapter` and extends these surfaces: frontend.

- Status: Available
- Tier: core
- Bundle: frontend
- Composer package: `capell-app/inertia-react-adapter`
- Namespace: `Capell\InertiaReactAdapter`
- Theme key: not applicable

## Technical Shape

### Service providers

- `Capell\InertiaReactAdapter\Providers\InertiaReactAdapterServiceProvider`

### Health checks

- `Capell\InertiaReactAdapter\Health\InertiaReactAdapterHealthCheck`

### Cache tags

- `inertia-react`


## Data Model

This package has no schema impact. It registers runtime behaviour through `Capell\InertiaReactAdapter\Providers\InertiaReactAdapterServiceProvider` while persistence remains with Capell core or required packages.

<!-- prettier-ignore-end -->
