---
layout: default
title: Tools
---

# Tools

Each workspace and desktop is composed of tools. A plugin can define new tools.
Each time the CoreBundle is opening a tool, it'll fire the event `open_tool_workspace|desktop_*claroline_mytool*`.

## Tools implementation

### Tool definition

Your plugin must define its properties, and the list of its tools in the *Resources/config/config.yml file*.

```yml
plugin:
    # Tools declared by your plugin.
    tools:
    - name: claroline_mytool
        **Currently using classes (prototype). Implementation of css classes not done yet**
        #class: res_text.png
        is_displayable_in_workspace: true
        is_displayable_in_desktop: true
        tool_rights: (optional)
            - name: action_name
              granted_icon_class: fa fa-something
              denied_icon_class: fa fa-something_2
```

In order to catch the event, your plugin must define a listener in your config.

This example will show you the main files of a basic HTML5 video player.

### Tool listener definition

**The listener config file**

*Claroline\VideoPlayer\Resources\config\services\listener.yml*

```yml
Claroline\ExampleBundle\Listener\ToolListener:
    tags:
        - { name: kernel.event_listener, event: open_tool_workspace_claroline_my_tool, method: onWorkspaceOpen }
        - { name: kernel.event_listener, event: open_tool_desktop_claroline_my_tool, method: onDesktopOpen }
        - { name: kernel.event_listener, event: configure_workspace_tool_claroline_my_tool, method: onWorkspaceConfigure }
        - { name: kernel.event_listener, event: configure_desktop_tool_claroline_my_tool, method: onDesktopConfigure }
```

### Listener implementation

```php

use Claroline\CoreBundle\Event\Tool\OpenToolEvent;

class MyToolListener
{
    public function onWorkspaceOpen(OpenToolEvent $event)
    {
        $event->setData([]);
    }
    
    public function onDesktopOpen(OpenToolEvent $event)
    {
        $event->setData([]);
    }
}
```

As you can see, if a tool is displayed in a workspace, you can know the current context
using `$event->getWorkspace();`.

## Translations

* tools.xx.yml

We use lower case for every translation keys.

Create the *tools* file in your Resources/translations folder.
You can translate your widget names here.

```json
{
    "claroline_my_tool": "My tool name"
}
```

Where `claroline_my_tool` is the name you defined in your config file.

Right management
----------------

Both workspace and desktop are an aggregation of tools.

There is a Voter which will determine which user can access which tool in a workspace.
(Currently a user can access every tool in its desktop)
When you must know if a user has access to a tool, you can use

```php
if (!$this->get('security.authorization_checker')->isGranted($toolName, $workspace)) {
    throw new AccessDeniedException();
}
```

*Where $toolName is your tool name and $workspace is the current workspace.*
