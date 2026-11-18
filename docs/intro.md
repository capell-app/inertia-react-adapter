A React team switches Capell Inertia over to React with one setting. Vue is the default, and setting CAPELL_INERTIA_ADAPTER to react swaps in this adapter. Only one adapter runs on a site, so you pick one.

Install it and your build gets React 19, Inertia's React library and Vite's React integration. Its script runs only when React is chosen, and only on pages shown through Inertia. The server cleans page and widget HTML before React sees it.

Content, image and title widgets each have a React component. Other widget types use the content component. That shows only a title and content. The components add class names such as capell-inertia-page but no styles, so you set the look. Each page shows its title, its content and each layout container with its widgets.
