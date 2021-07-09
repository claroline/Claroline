---
layout: default
title: Widgets
---

# Widgets


## Plugin configuration file

Your plugin must define its properties and the list of its widgets in the
*Resources/config/config.yml file*.

This file will be parsed by the plugin installator to install your plugin and
create all your declared widgets in the database.

```yml
plugin:
    # Widgets declared by your plugin.
    widgets:
          # Each widget requires a name.
        - name: claroline_exemple
          # Set this to true if the widget is configurable
          is_configurable: true
          # You can set an icon for your widget. The icon must be in your public/images/icons folder.
          icon: something.jpeg
        
        - name: claroline_theanswertolifeuniverseandeverything
          is_configurable: false
```

## Translations

* widget.xx.yml

We use lower case for every translation keys.

Create the *widget* file in your Resources/translations folder.
You can translate your widget names here.

```json
{
 "mywidgetname": "mytranslation"
}
```

Where `mywidgetname` is the name you defined in your config file.
