# PathBundle

![SensioLabs Insight][1]

## Installation

Install with composer :

    $ composer require "innova/path-bundle" "2.*" 
    $ php app/console claroline:plugin:install InnovaPathBundle

## Uninstall 

    $ php app/console claroline:plugin:uninstall InnovaPathBundle 

## Command line

The PathBundle provides a new Symfony 2 command to publish Paths into the application :

    $ php app/console innova:path:publish

It accepts the following arguments :

```
--force, -f
```

Additional to Paths which need publishing (not already published or pending modifications), the command will publish Paths which are not flagged to publish.

```
--workspace[=workspaceId], -w[=workspaceId]
```

Publish only Paths which are in this Workspace.

```
--path[=pathId], -p[=pathId]
```

Publish a specific Path.

## Authors

* Donovan Tengblad (purplefish32)
* Axel Penin (Elorfin)
* Arnaud Bey (arnaudbey)
* Eric Vincent (ericvincenterv)

[1]: https://insight.sensiolabs.com/projects/91c3195e-8056-40e9-b1d3-e5cc10230e4f/small.png
