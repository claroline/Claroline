---
layout: default
title: Tools
---

# Tools

> This section only covers the API part of the tools.
> To know how to declare and register UI for your tool, please see User Interface > Tools


Desktop, Administration and Workspaces are composed of multiple tools.
A plugin can define new tools.


## Tool definition

Your plugin must define the list of its tools in the *MY_PLUGIN/Resources/config/config.yml* file.

```yml
plugin:
    # Tools declared by your plugin
    tools:
        - name: my_tool
          class: icon # a FontAwesome icon name to display in menus (MUST NOT contain the fa- prefix)
          tool_rights: # (optional)
              - name: action_name
```

> When adding a new tool in the `config.yml` of a plugin, you'll need to run a claroline:update
> to see it appear in the application

## Tool definition

In order to catch the event, your plugin must define a subscriber in your config.

### The tool config file

Tools need to be registered in the symfony service container with a `claroline.component.tool` tag.

*MY_PLUGIN\Resources\config\components\tool.yml*

```yml
services:
    Vendor\PluginNameBundle\Component\Tool\MyToolTool:
        parent: Claroline\AppBundle\Component\Tool\AbstractTool
        tags: [ 'claroline.component.tool' ]
```

### The tool class

```php
namespace Vendor\PluginNameBundle\Component\Tool;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\AccountContext;
use Claroline\CoreBundle\Component\Context\AdministrationContext;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\PublicContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;

class MyToolTool extends AbstractTool
{
    public static function getName(): string
    {
        return 'my_tool';
    }
    
    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            AccountContext::getName(),
            AdministrationContext::getName(),
            DesktopContext::getName(),
            PublicContext::getName(),
            WorkspaceContext::getName(),
        ])
    }

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        return [];
    }

    public function configure(string $context, ContextSubjectInterface $contextSubject = null, array $configData = []): ?array
    {
        return [];
    }
}
```

As you can see, if a tool is displayed in a workspace, you can know the current context
using `$contextSubject`.

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
> $ rm -rf var/cache/* && php bin/console bazinga:js-translation:dump public/js --format=js --merge-domains && rm -rf var/cache/*
