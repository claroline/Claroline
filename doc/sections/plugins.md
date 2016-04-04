[[Documentation index]][index_path]

Claroline Plugins
=================

- [Directory structure](#directory-structure)
- [Plugin configuration file](#plugin-configuration-file)
- [Translations](#translations)
  - [Plugin description](#plugin-description)

Directory structure
-------------------

Sources are located in the *vendor* folder. This is were your plugin should be
located.

As plugins are Symfony2 [bundles][practices], it is strongly recommanded that
you follow bundles naming rules. So the folder of your plugin should be like
this: *vendor/myVendorName/myPluginNameBundle*.

Plugin configuration file
-------------------------

Your plugin must define its properties in *Resources/config/config.yml file*.
This file must at least include:

```yml
plugin:
    # Set this to "true" if your plugin must have an entry in the plugin configuration page.
    has_options: 'true'
    # You can set an icon for your plugin. The icon must be in your public/images/icons folder.
    icon: 'icon.png'
```

Translations
------------

Each plugin require several translations domains:

* plugin_description

We use lower case for every translation keys.

### Plugin description

Create the *plugin_description* file in your *Resources/translations* folder.

<pre>
bundle
+-- Resources
    +-- translations
        +-- plugin_description.en.yml
</pre>


Here is the translation key used to translate your plugin name:

```yml
myvendorbundleshortname: 'this is a translation'
```

[index_path]: ../index.md

Full plugin configuration file example:

```yml
plugin:
    has_options: 'false'
    #icon: 'icon.png'

widgets:
   - name: 'claroline_mywidget1'
     #is_configurable: 'false'
     is_exportable: 'true'
     #- name: 'claroline_mywidget2'
     #is_configurable: 'false'
     #is_displayable_in_workspace: 'true'
     #is_displayable_in_desktop: 'true'

resources:
    - class: 'Claroline\ExampleBundle\Entity\Example'
      name: 'claroline_example'
      is_exportable: 'false'
      icon: 'res_text.png'

tools:
    - name: 'claroline_mytool'
      #class: 'res_text.png'
      #is_exportable: 'true'
      is_displayable_in_workspace: 'true'
      is_displayable_in_desktop: 'true'
```

The plugin section contains the general options of the plugin.
The has_options field is required. This field will generate a link in the
platform administrations wich will fire an event in wich you can send a form
and set some general parameters of your plugin.

[[Documentation index]][index_path]

[practices]: http://symfony.com/doc/2.0/cookbook/bundles/best_practices.html
