## What it does

Capell Inertia React Adapter registers the React client runtime, Vite application entrypoint, NPM dependencies, and `Capell/Page` component metadata for the shared Capell Inertia bridge.

## Do I need to do anything?

Editors usually do nothing. Integrators install this package alongside `capell-app/inertia`, set `CAPELL_INERTIA_ADAPTER=react`, and include the generated Vite assets in the application's normal frontend build.

## Where it shows up

There is no package-owned admin screen or setting. Its application asset is contributed only when a public page uses the Inertia runtime and `react` is the selected adapter.

## Good to know

- Install this only when the consuming Inertia theme or package uses React.
- It works alongside the base Capell Inertia bridge; the bridge owns the shared renderer and middleware.
- The adapter readiness check in Diagnostics confirms the registered package, Vite build path, and application entrypoint.
- Do not select this and the Vue adapter for the same active Inertia runtime; select the adapter that matches the consuming frontend.
