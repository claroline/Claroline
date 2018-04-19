---
layout: default
title: Core plugin
---

# Core plugin

The **ClarolineCoreBundle** is a single bundle that provides:

- Platform organization (main sections, navigation, authentication, logs).
- Base model (Workspace, Users, Resources).
- Plugin system.


## Directory structure

The directory structure have some differences of a classic bundle, there is
some special directories as follows:

```
bundle
|-- Command                 (Dev commands)
|-- Library                 (Services)
|-- Migrations              (Data Base description)
|-- Resources
|-- +-- themes              (Default claroline themes)
|   +-- views
+-- Templating              (Overwriting of a Symfony Class)
```

## Platform organization

Sections (controller + layout):

```
base -> core::layout -> admin/desktop/workspace::layout -> ...
```


## Model

- Workspace
- User/Group
- Resource
- Roles/Permissions
- Badge/Group


## Plugin system

The [Claroline plugins](sections/application/plugins.md) was designed as a modular system and they must be in
heir own repositories.
