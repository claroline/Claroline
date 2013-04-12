[[Documentation index]][index_path]

## Directory structure

You must put the code of your plugin in the *src/plugin* folder.

You may choose to develop in the *Claroline* vendor folder or create your own one (e.g. *src/plugin/myVendorName*). There you create a new folder for each plugin.

As plugins are Symfony2 [bundles](http://symfony.com/doc/2.0/cookbook/bundles/best_practices.html), it is strongly recommanded that you follow bundles naming rules. So the folder of your plugin should be like this: *src/plugin/myVendorName/myPluginNameBundle*.

## Plugin configuration file

Your plugin must define its properties in *Resources/config/config.yml file*.

    plugin:
        # Set this to "true" if your plugin must have an entry in the plugins configuration page.
        has_options: true
        # You can set an icon for your plugin. The icon must be in your public/images/icons folder.
        icon: icon.png

## Translations

Each plugin require several translations domains:

* plugin_description

We use lower case for every translation keys.

### plugin_description

Create the *plugin_description* file in your Resources/translations folder.

    plugin_description.en.yml

Here is the translation key used to translate your plugin name:

    myvendorbundleshortname : this is a translation

eg:

    clarolineexample: exemple


[index_path]: ../index.md

Full plugin configuration file example:

    plugin:
      has_options: false
      #icon: icon.png

      widgets:
        - name: claroline_mywidget1
    #     is_configurable: false
          is_exportable: true
    #   - name: claroline_mywidget2
    #     is_configurable: false

      resources:
        - class: Claroline\ExampleBundle\Entity\Example
          name: claroline_example
          is_browsable: true
          is_exportable: false
          icon: res_text.png

      tools:
        - name: claroline_mytool
          #class: res_text.png
          #is_exportable: true
          is_displayable_in_workspace: true
          is_displayable_in_desktop: true

The plugin section contains the general options of the plugin.
The has_options field is required. This field will generate a link in the
platform administrations wich will fire an event in wich you can send a form and
set some general parameters of your plugin.

The event name format is plugin_options_myvendormybundle and
the $event class is use Claroline\CoreBundle\Library\Event\PluginOptionsEvent.

This $event will be asking you to return a response. Your twig file must extends
{% extends "ClarolineCoreBundle:Administration:layout.html.twig" %} if you want
to keep the administration layout.


