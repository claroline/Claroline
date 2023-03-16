---
layout: default
title: Tools
---

# Tools

> Before declaring a user interface for your tool, 
> you'll first need to [register it](Claroline/sections/application/tools) in the Claroline Connect application.

## Registering a tool into Claroline Connect

The UI for your tool will need to be registered in the *MY_PLUGIN/Resources/modules/plugin.js* file.

```js
import {registry} from '#/main/app/plugins/registry'

registry.add('VendorPluginNameBundle', {
  tools: {
    'my_tool': () => {
      return import(/* webpackChunkName: "plugin-name-tools-my_tool" */ '#/plugin/my-plugin/tools/my-tool')
    }
  },
  administration: {
    'my_admin_tool': () => {
      return import(/* webpackChunkName: "plugin-name-admin-my_admin_tool" */ '#/plugin/my-plugin/administration/my-admin-tool')
    }
  }
})
```

Where `#/plugin/my-plugin/tools/my-tool` is the path (with webpack aliases) to the directory containing the source code of your tool UI.

> When declaring new elements in the `plugin.js`, you may need to stop and restart
> the webpack dev server.


## Declaring a tool app

Tools apps are stored in `MY_PLUGIN/Resources/modules/tools` for desktop/workspace tools
and in `MY_PLUGIN/Resources/modules/administration`.
Each tool MUST have its own directory.

The entrypoint of the tool app exposes the components and the redux store of the tool.
It's located in an `index.js` file at the root of your tool module directory.

### Minimal configuration

```js
// MY_PLUGIN/Resources/modules/tools/my-tool/index.js

import {MyToolTool} from '#/plugin/my-plugin/tools/my-tool/components/tool'

export default {
  component: MyToolTool
}
```

### Full configuration

```js
// MY_PLUGIN/Resources/modules/tools/my-tool/index.js

import {MyToolTool} from '#/plugin/my-plugin/tools/my-tool/containers/tool'
import {MyToolMenu} from '#/plugin/my-plugin/tools/my-tool/components/menu'

import {reducer} from '#/plugin/my-plugin/tools/my-tool/store'

export default {
  /**
   * [Required] The root component of the tool.
   */
  component: MyToolTool,
  
  /**
   * [Optional] A menu component to display in the Claroline sidebar.
   */
  menu: MyToolMenu,
  
  /**
   * [Optional] A reducer to mount in the Claroline store to manage the custom tool data.
   */
  store: reducer,
  
  /**
   * [Optional] Additional styles to append to the page when opening the tool.
   */
  styles: ['claroline-distribution-plugin-my-plugin-my-tool']
}
```


## Creating the tool components tree

```js
// MY_PLUGIN/Resources/modules/tools/my-tool/components/tool.jsx

import React from 'react'

import {ToolPage} from '#/main/core/tool/containers/page'

const MyToolTool = (props) =>
  <ToolPage>
    Hello World
  </ToolPage>

export {
  MyToolTool
}
```

> Each tool page MUST starts its rendering with a `ToolPage` (most tools will integrate some `Routes` before it)


## Declaring a tool store

The store for a tool is located in a subdirectory `store` of the tool.

> All store components (actions, reducer, selectors) MUST be exposed and consumed through a [barrel file](https://github.com/basarat/typescript-book/blob/master/docs/tips/barrel.md).

### Creating a custom reducer for the tool

The first thing you'll need to declare is a reducer to handle the custom data of your tool.

```js
// MY_PLUGIN/Resources/modules/tools/my-tool/store/reducer.js

import {makeReducer} from '#/main/app/store/reducer'

const reducer = makeReducer(initialeState = null, handlers = {})

export {
  reducer
}
```

> **ATTENTION :** Don't forget to declare the new reducer in the tool app.
> It will not be mounted in the application store otherwise.

The tool reducer will be mounted in a sub object (with the tool name as a key) in the application store.

We need to create the first selector for our tool. 
Every future selector will be composed based on this one using [reselect](https://github.com/reactjs/reselect#reselect).

```js
// MY_PLUGIN/Resources/modules/tools/my-tool/store/selectors.js

// the name of our tool store which is equal to the name of our tool
const STORE_NAME = 'my_tool'

// selector to get the whole tool store
const store = (state) => state[STORE_NAME]

// more selectors created with `createSelector()`
// ...

export const selectors = {
  STORE_NAME,
  
  store
}
```

### Catching custom data returned the tool API

> You'll need to have created the Subscriber for your tool in the API in order to get custom data.

```php
namespace Vendor\PluginNameBundle\Subscriber;

use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
// ...

class MyToolSubscriber implements EventSubscriberInterface
{
    const NAME = 'my_tool';
    
    // ...
    
    public function onOpen(OpenToolEvent $event): void
    {
        $event->setData([
            // You can put here the serialized data which need be loaded when the tool is opened
            'my_data_key' => [/* serializable structure */], 
        ]);
    }
}
```

```js
// MY_PLUGIN/Resources/modules/tools/my-tool/store/reducer.js

import {makeInstanceAction} from '#/main/app/store/actions'

import {selectors} from '#/plugin/my-plugin/tools/my-tool/store/selectors'

const reducer = makeReducer(null, {
  [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.my_data_key,
})
```

You can now access your API data with your selectors : 

```js
import {selectors} from '#/plugin/my-plugin/tools/my-tool/store/selectors'

// From a Container
const MyContainer = connect(
  (state) => ({
    myData: selectors.store(state)
  })
)(MyComponent)

// From another selector
const data = createSelector(
  [selectors.store],
  (myData) => console.log(myData)
)
```

### Connecting the tool to the store

In order to retrieve your data stored in redux, you'll need to connect your components tree to the redux store.
This is done through `containers`.

```js
// MY_PLUGIN/Resources/modules/tools/my-tool/containers/tool.jsx

import {connect} from 'react-redux'

import {MyToolTool as MyToolToolComponent} from '#/plugin/MY_PLUGIN/tools/my-tool/components/tool'
import {selectors} from '#/plugin/MY_PLUGIN/tools/my-tool/store'

const MyToolTool = connect(
  (state) => ({
    // inject Redux data inside your component
    // your component will now receive a prop `myData` filled with the selected data from the store
    myData: selectors.store(state)
  }),
  (dispatch) => ({
    // inject Redux actions inside your component
  })
)(MyToolToolComponent)

export {
  MyToolTool
}
```

> Containers MUST have the same name than the components they connect to the store.

> **ATTENTION :** Don't forget to replace the root component of your tool app with your new container.

You can now manipulate the passed data into your component :

```js
// MY_PLUGIN/Resources/modules/tools/my-tool/components/tool.jsx

import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ToolPage} from '#/main/core/tool/containers/page'

const MyToolTool = (props) =>
  <ToolPage>
    // do something with `props.myData`
    {props.myData.map(object => console.log(object))}
  </ToolPage>

MyToolTool.propTypes = {
  myData: T.array
}

export {
  MyToolTool
}
```

## Accessing the tool generic store

In addition to your custom data, Claroline Connect stores and gives access to various information about the tool 
currently opened (eg. current user rights in the tool, the tool path).

All this information are accessible through some selectors :

```js
import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

// ...

// retrieves the base path of the tool
toolSelectors.path(state)

// retrieves information (permissions, name, poster, ...) about the tool
toolSelectors.toolData(state)
```


## Adding styles to a tool

The styles for your tool will need to be registered in the *MY_PLUGIN/assets.json* file.
Tools styles are stored in `MY_PLUGIN/Resources/styles`.

```json
{
  "styles": {
    "entry": {
      "my-tool": "my-tool.less"
    }
  }
}
```

```less
// MY_PLUGIN/Resources/styles/my-tool.less

// Claroline & bootstrap variables
@import "/src/main/app/Resources/styles/variables";

// Your custom styles are defined here

```

> When declaring new elements in the `assets.json` or when changes modify the styles,
> you will need to run `php bin/console claroline:theme:build` to see the changes.
