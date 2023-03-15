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
