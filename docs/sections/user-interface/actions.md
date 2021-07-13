---
layout: default
title: Actions
---

# Actions

An action is anything the user can do to make changes in the application (eg. navigation, data loading).

It provides an low-level action API for apps.
It provides a common way to describe an action.
It exposes components which render appropriate buttons for actions 
(aka. it respects HTML semantics and offers the best accessibility possible).


## Usages

As a component, to render an agnostic button that will trigger the action.

The button is rendered without any styles by default, so it can be rendered anywhere.
If you want to add styles (like standard Bootstrap btn styles), you can add a `className`.

```JSX
// ...
import {Button} from '#/main/app/action/components/button'

// ...

const ParentComponent = () =>
    <div>
      <Button
        id="my-action"
        className="btn"
        type="url"
        label="An awesome action"
        target="http://www.claroline.com"
      />
    </div>

```

As an object, for base components which use actions in their config (eg. `DataList`, `DataCard`, `ResourcePage`, `UserPage`).

```js
const actions = {
  id: 'my-action',
  type: 'url',
  label: 'An awesome action',
  target: 'http://www.claroline.com'
}
```


## Properties

### Commons

- **type** [async\|callback\|download\|email\|link\|modal\|popover\|url] _(required)_

The type of the action. Each action type have custom props for configuration (see below).


- **label** [string] _(required)_

The translated name of the action. It MUST be an action, not a noun (ex. use "Show results", not "Results").


- **icon** [string]

An icon that represent the action. For now only Font based icons are supported.

- **disabled** [bool]

Defines if the action should be visible or not.


- **displayed** [bool]

Defines if the action should be visible or not.
This is used when a user will never have access to the action (most of the time it's for security implementation).

For cases when the user can be granted the access later (ex. results become available when user have passed the evaluation), you should `disabled` instead. 

Only interpreted in components that takes list of actions configurations (ex. `actions` prop in `DataList`, `DataCard`).


### Inherited from the Button API
When using the `Button` component to trigger an action, you can also configure the underlying API
responsible of rendering button components (for action objects, styles are correctly sets by the components that uses it).

- **className** [string]

Additional classes to add some styles to the rendered button. Use `btn` or `btn-link` for standard Bootstrap button styles.

- **size** [sm|lg]

The rendering size of the button. It should be implemented by the btn class you use in `className`.


## Action types

### Async
An action that will start an AJAX call when triggered.

#### Properties

#### Examples


### Callback
An action that will execute a defined callback when triggered.

#### Properties

#### Examples


### Download

#### Properties

#### Examples


### Email

#### Properties

#### Examples


### Link
An action that will navigate the user inside the application when triggered.

#### Properties

#### Examples


### Modal
An action that will open a modal when triggered.

#### Properties

#### Examples


### Popover (TODO)
An action that will open a popover when triggered.

#### Properties

#### Examples


### URL
An action that will navigate the user to an URL when triggered (either an external URL or another app inside Claroline).

#### Properties

#### Examples


## Important note

You MUST always navigate inside the app Router through the action API. This is the only way to ensure it will use the correct history (we have 2 : BrowserHistory for regular apps and MemoryHistory for embedded apps like widgets and resources).
