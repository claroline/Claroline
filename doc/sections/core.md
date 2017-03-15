[[Documentation index]][1]

Core architecture
=================

- [Introduction](#introduction)
- [Directory structure](#directory-structure)
- [Platform organization](#platform-organization)
- [Model](#model)
- [Plugin system](#plugin-system)

Introduction
------------

The **ClarolineCoreBundle** is a single bundle that provides:

- Platform organization (main sections, navigation, authentication, logs).
- Base model (Workspace, Users, Resources).
- Plugin system.


Directory structure
-------------------

The directory structure have some differences of a classic bundle, there is
some special directories as follows:

<pre>
bundle
|-- Command                 (Dev commands)
|-- Library                 (Services)
|-- Migrations              (Data Base description)
|-- Resources
|-- +-- themes              (Default claroline themes)
|   +-- views
+-- Templating              (Overwriting of a Symfony Class)
</pre>

Platform organization
---------------------

Sections (controller + layout):

<pre>
base -> core::layout -> admin/desktop/workspace::layout -> ...
</pre>

Model
-----

- Workspace
- User/Group
- Resource
- Roles/Permissions
- Badge/Group

Plugin system
-------------

The [Claroline plugins][2] was designed as a modular system and they must be in
heir own repositories.

[[Documentation index]][1]

[1]: ../index.md
[2]: plugins.md

