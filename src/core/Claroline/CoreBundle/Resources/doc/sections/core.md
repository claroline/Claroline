[[Documentation index]][1]

Core
====

core = 1 bundle
provides:
    platform organization (main sections, navigation, auth, logs)
    base model (ws, usr, rsc)
    plugin system


Directory structure
-------------------

classic bundle
special dirs:
    command (dev)
    library (services)
    res/less...

Platform organization
---------------------

sections (controller + layout)
base -> core::layout -> admin/desktop/workspace::layout -> ...

Model
-----

ws
usr/gp
rsrc
roles/permissions

Plugin system
-------------

modular system
plugins in their own repository


[1]: ../index.md