---
layout: default
title: Tools
---

# Tools

Desktop, Administration and Workspaces are composed of multiple tools.
A plugin can define new tools.

Each time the CoreBundle is opening a tool, 
it'll fire the event `open_tool_workspace|desktop_*claroline_my_tool*`.

## Tools implementation

### Tool definition

Your plugin must define its properties, and the list of its tools in the *Resources/config/config.yml file*.

```yml
plugin:
    # Tools declared by your plugin.
    tools:
        - name: claroline_my_tool
          class: icon # a FontAwesome icon name to display in menus (MUST NOT contain the fa- prefix)
          is_displayable_in_workspace: true
          is_displayable_in_desktop: true
          tool_rights: # (optional)
              - name: action_name

    # Admin tools declared by your plugins
    admin_tools:
        - name: claroline_my_admin_tool
          class: icon # a FontAwesome icon name to display in menus (MUST NOT contain the fa- prefix)
```

> When adding a new tool in the `config.yml` of a plugin, you'll need to run a claroline:update
> to see it appear in the application

### Tool subscriber definition

In order to catch the event, your plugin must define a subscriber in your config.

**The listener config file**

*MY_PLUGIN\Resources\config\services\subscriber.yml*

```yml
services:
    Claroline\ExampleBundle\Subscriber\MyToolSubscriber:
        tags: [ kernel.event_subscriber ]
```

### Subscriber implementation

```php
namespace Claroline\ExampleBundle\Subscriber;

use Claroline\CoreBundle\Entity\Tool\AbstractTool;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;

class MyToolSubscriber implements EventSubscriberInterface
{
    const NAME = 'claroline_my_tool';
    
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

* tools.xx.yml

We use lower case for every translation keys.

Create the *tools* file in your MY_PLUGIN/Resources/translations folder.
You can translate your tool names here.

```json
{
    "claroline_my_tool": "My tool name"
}
```

Where `claroline_my_tool` is the name you defined in your config file.
