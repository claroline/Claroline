[[Documentation index]][index_path]

[index_path]: ../index.md

Full plugin configuration file example:

    plugin:
      has_options: false
      #icon: icon.png

      widgets:
        - name: claroline_mywidget1
          is_configurable: false
    #   - name: claroline_mywidget2
    #     is_configurable: false

      resources:
        - class: Claroline\ExampleBundle\Entity\Example
          name: claroline_example
          is_visible: true
          is_browsable: true
          icon: res_text.png

      tools:
        - name: claroline_mytool
          #class: res_text.png
          is_displayable_in_workspace: true
          is_displayable_in_desktop: true

The plugin section contains the general options of the plugin.
The has_options field is required. This field will generate a link in the
platform administrations wich will fire an event in wich you can send a form and
set some general parameters of your plugin.

The event name format is plugin_options_myvendormybundle and
the $event class is use Claroline\CoreBundle\Library\Plugin\Event\PluginOptionsEvent.

This $event will be asking you to return a response. Your twig file must extends
{% extends "ClarolineCoreBundle:Administration:layout.html.twig" %} if you want
to keep the administration layout.


