# PathBundle

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/717ec409-89d6-483b-ad4e-26e1ddb5edbc/small.png)](https://insight.sensiolabs.com/projects/717ec409-89d6-483b-ad4e-26e1ddb5edbc)

## Installation

Install with composer :

    $ composer require "innova/path-bundle" "6.*"
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

## Requests

Go to [Claroline](https://github.com/claroline/Claroline/issues) if you want to ask for new features.

Go to [Claroline Support](https://github.com/claroline/ClaroSupport/issues) if you encounter some bugs.

## Authors

* Donovan Tengblad (purplefish32)
* Axel Penin (Elorfin)
* Arnaud Bey (arnaudbey)
* Eric Vincent (ericvincenterv)
* Nicolas Dufour (eldoniel)
