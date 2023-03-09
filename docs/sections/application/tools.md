---
layout: default
title: Tools
---

# Tools

Desktop, Administration and Workspaces are composed of multiple tools.
A plugin can define new tools.

Each time the CoreBundle is opening a tool, 
it'll fire the event `open_tool_workspace|desktop_*my_tool*`.

## Tool definition

Your plugin must define the list of its tools in the *MY_PLUGIN/Resources/config/config.yml* file.

```yml
plugin:
    # Tools declared by your plugin
    tools:
        - name: my_tool
          class: icon # a FontAwesome icon name to display in menus (MUST NOT contain the fa- prefix)
          is_displayable_in_workspace: true
          is_displayable_in_desktop: true
          tool_rights: # (optional)
              - name: action_name

    # Admin tools declared by your plugin
    admin_tools:
        - name: my_admin_tool
          class: icon # a FontAwesome icon name to display in menus (MUST NOT contain the fa- prefix)
```

> When adding a new tool in the `config.yml` of a plugin, you'll need to run a claroline:update
> to see it appear in the application

## Tool subscriber definition

In order to catch the event, your plugin must define a subscriber in your config.

### The subscriber config file

*MY_PLUGIN\Resources\config\services\subscriber.yml*

```yml
services:
    Vendor\PluginNameBundle\Subscriber\MyToolSubscriber:
        tags: [ kernel.event_subscriber ]
```

### The subscriber class

```php
namespace Vendor\PluginNameBundle\Subscriber;

use Claroline\CoreBundle\Entity\Tool\AbstractTool;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MyToolSubscriber implements EventSubscriberInterface
{
    const NAME = 'my_tool';
    
    public static function getSubscribedEvents(): array
    {
        return [
            // For desktop tools
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::DESKTOP, static::NAME) => 'onOpen',
            // For workspace tools
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::WORKSPACE, static::NAME) => 'onOpen',
            // For admin tools
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::ADMINISTRATION, static::NAME) => 'onOpen',
        ];
    }
    
    public function onOpen(OpenToolEvent $event): void
    {
        $event->setData([
            // You can put here the serialized data which need be loaded when the tool is opened 
        ]);
    }
}
```

As you can see, if a tool is displayed in a workspace, you can know the current context
using `$event->getWorkspace();`.

## Translations

* tools.xx.json

We use lower case for every translation keys.

Create the *tools* file in your `MY_PLUGIN/Resources/translations` folder.
You can translate your tool names here.

```json
{
    "my_tool": "My tool name"
}
```

Where `my_tool` is the name you defined in your config file.

> You'll need to rebuild the js translation in order to see the changes in your application.
> 
> $ rm -rf var/cache/* && php bin/console bazinga:js-translation:dump public/js && rm -rf var/cache/*

## Tool UI definition

The UI for your tool will need to be registered in the *MY_PLUGIN/Resources/modules/plugin.js* file

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

## Tool UI app

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
  component: MyToolTool,
  menu: MyToolMenu,
  store: reducer,
  styles: ['claroline-distribution-plugin-my-plugin-my-tool']
}
```

### First component

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

## Tool styles definition

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

> When declaring new elements in the `assets.json` or when change modify the style, 
> you will need to run `php bin/console claroline:theme:build` to see the changes.
