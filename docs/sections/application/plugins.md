---
layout: default
title: Plugins
---

# Plugins

All the Claroline Connect features are injected through plugins.

Plugins are symfony bundles which implement a custom interface : `Claroline\KernelBundle\Bundle\PluginBundleInterface`.

There are 3 ways to create new plugins for Claroline Connect :
- Directly into the Claroline Connect package.
- By declaring a "Distribution" package (which can introduce multiple plugins at once).
- By declaring a "Plugin" package. **Not supported yet**

Plugins can inject :
  - Resources (API + UI)
  - Tools (API + UI)
  - DataSources (API + UI)
  - Account tabs (UI)
  - Actions for the base entities (eg. User, Workspace, Resource)
  - Header widgets (UI)


# Declaring a new plugin

## API

### The bundle class **[required]**

Plugins are standard [symfony bundles](https://symfony.com/doc/current/bundles.html) (aka class extending `Symfony\Component\HttpKernel\Bundle\Bundle`)
and follow the same naming standards.

In addition to the `PluginBundleInterface`, Claroline Connect declares some abstract classes (which inherit from `Bundle`) to ease the declaration
of the Plugin bundle file.

The abstract class to implement depends on the way the plugin is provided to Claroline Connect.

- Main package plugin : `Claroline\KernelBundle\Bundle\DistributionPluginBundle`.
- Custom distribution plugin : `Claroline\KernelBundle\Bundle\ExternalPluginBundle`.
- Custom package plugin : **Not supported yet**

> Like in standard symfony bundle, in most of the case, there is nothing to implement into the bundle class.
It's only used to declare the plugin to the base system, and all the required implementations are provided by
the plugin abstract classes.

### The plugin config file **[required]**

Plugins also require a config file (written in yaml) in order to inject new features (like resources and tools) into the Claroline Connect application.
This file is located in `your_plugin/Resources/config/config.yml`.

```yaml
plugin: ~
```

### The composer autoloading **[required]**

Claroline Connect uses a custom directory structure which don't fully follow the PSR-4 rules 
(there are additional directories before the PSR-4 can be applied).

You'll need to declare it to the `composer` autoloader in the `composer.json` file.

In Claroline Connect main package :

```json
"autoload": {
    "psr-4": {
        "Vendor\\PluginNameBundle\\VendorPluginNameBundle": "src/plugin/plugin-name"
    }
}
```

In external distribution :

```json
"autoload": {
    "psr-4": {
        "Vendor\\PluginNameBundle\\VendorPluginNameBundle": "plugin/plugin-name"
    }
}
```

Composer automatically updates its autoloader when running `composer update` or `composer install`.

When creating a new plugin, you'll need to manually declare it to the composer autoloader :

```bash
$ composer dump-autoload
``` 

## UI

### The plugin config file **[required]**

Plugins require a config file (written in js) in order to inject new elements (like resources and tools) into the Claroline Connect user interface.
This file is located in `MY_PLUGIN/Resources/modules/plugin.js`.

```js
import {registry} from '#/main/app/plugins/registry'

registry.add('VendorPluginNameBundle', {
  tools: {
    // ...
  },
  resources: {
    // ...
  }
  // ... Other elements to inject into the UI
})
```
