[[Documentation index]][1]

# Tools

Each workspace and desktop is composed of tools. A plugin can define new tools.
Each time the CoreBundle is opening a tool, it'll fire the event
open_tool_workspace|desktop_*claroline_mytool*

## Tools implemetation

### Tool definition

Your plugin must define its properties and the list of its tools in the *Resources/config/config.yml file*.

```yml
plugin:
    # Tools declared by your plugin.
    tools:
    - name: claroline_mytool
        **Currently using classes (prototype). Implementation of css classes not done yet**
        #class: res_text.png
        is_displayable_in_workspace: true
        is_displayable_in_desktop: true
        has_options: false (default = false: does the tool need a configuration page)
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
claroline.listener.example_tool:
    class: Claroline\ExampleBundle\Listener\ToolListener
    calls:
    - [setContainer, ["@service_container"]]
    tags:
    - { name: kernel.event_listener, event: open_tool_workspace_claroline_mytool, method: onWorkspaceOpen }
    - { name: kernel.event_listener, event: open_tool_desktop_claroline_mytool, method: onDesktopOpen }
    - { name: kernel.event_listener, event: configure_workspace_tool_claroline_mytool, method: onWorkspaceConfigure }
    - { name: kernel.event_listener, event: configure_desktop_tool_claroline_mytool, method: onDesktopConfigure }
```

### Listener implementation

```php
public function onWorkspaceOpen(DisplayToolEvent $event)
{
    $event->setContent($this->workspace($event->getWorkspace()->getId()));
}

public function onDesktopOpen(DisplayToolEvent $event)
{
    $event->setContent($this->desktop());
}

private function workspace($workspaceId)
{
    //if you want to keep the context, you must retrieve the workspace.
    $em = $this->container->get('doctrine.orm.entity_manager');
    $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\Workspace')->find($workspaceId);

    return $this->container->get('templating')->render(
        'ClarolineExampleBundle::workspace_tool.html.twig', array('workspace' => $workspace)
    );
}

private function desktop()
{
    return $this->container->get('templating')->render(
        'ClarolineExampleBundle::desktop_tool.html.twig'
    );
}
```

As you can see, if a tool is displayed in a workspace, you can know the current context
using $event->getWorkspace();
Then the workspace_tool.html.twig template must extends {% extends 'ClarolineCoreBundle:Workspace:layout.html.twig' %}

## Translations

* tools.xx.yml

We use lower case for every translation keys.

Create the *tools* file in your Resources/translations folder.
You can translate your widget names here.

```yml
claroline_mytool: mytranslation
```

Where mywidgetname is the name you defined in your config file.

Right management
----------------

Both workspace and desktop are an aggregation of tools.
A user can order the displayed toolbar and the "index" will always be the
first tool.

There is a Voter wich will determine wich user can access wich tool in a workspace.
(Currently a user can access every tools in its desktop)
When you must know if a user has access to a tool, you can use

```php
if (!$this->get('security.authorization_checker')->isGranted($toolName, $workspace)) {
    throw new AccessDeniedException();
}
```

*Where $toolName is your tool name and $workspace is the current workspace.*

Configuration
-------------

Each tool can declare a configuration page. The list of configurable tools is displayed
in the parameter tool.
Once a user clicks on the link, an event configure_workspace|desktop_tool_*toolname* is thrown.

You can the get the thrown event (Claroline\CoreBundle\Event\ConfigureWorkspaceToolEvent
or Claroline\CoreBundle\Event\ConfigureDesktopToolEvent) and set some content

```php
$event->setContent($this->templating->render(...));
```

Note: if you want to keep the layout, you must extends either

```html+jinja
{% extends 'ClarolineCoreBundle:Desktop:layout.html.twig' %}
```

or

```html+jinja
{% extends 'ClarolineCoreBundle:Workspace:layout.html.twig' %}
```

[[Documentation index]][index_path]

[1]: ../../index.md
