# Capell Inertia React Adapter

<!-- prettier-ignore-start -->

A React team switches Capell Inertia over to React with one setting. Vue is the default, and setting CAPELL_INERTIA_ADAPTER to react swaps in this adapter. Only one adapter runs on a site, so you pick one.

Install it and your build gets React 19, Inertia's React library and Vite's React integration. Its script runs only when React is chosen, and only on pages shown through Inertia. The server cleans page and widget HTML before React sees it.

Content, image and title widgets each have a React component. Other widget types use the content component. That shows only a title and content. The components add class names such as capell-inertia-page but no styles, so you set the look. Each page shows its title, its content and each layout container with its widgets.

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

---

For developers: see the [README](../README.md).

<!-- prettier-ignore-end -->
