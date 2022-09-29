---
layout: default
title: Plugins
---

# Plugins

All the Claroline Connect features are injected through plugins.

Plugins are symfony bundles which implement a custom interface : `Claroline\KernelBundle\Bundle\PluginBundleInterface`.

Plugins can inject :
  - Resources (API + UI)
  - Tools (API + UI)
  - DataSources (API + UI)
  - Account tabs (UI)
  - Header widgets (UI)
