[[Documentation index]][index_path]

Themes
======

Plugins can introduce platform themes. Once a plugin is installed, the theme
it provides becomes available in the *Appearance* section of the general 
parameters of the platform. As this feature is based on template overriding,
a plugin can customize any part of the user interface.

Themes are declared in the `themes` section of the plugin configuration:

```yml
plugin:
  # ...
  themes:
    - name: "Custom Theme"
```

Any template provided by a registered bundle can be overridden with a template
reproducing its path inside the *Resource/views* directory of the plugin. The 
general location pattern is:

*Resources/views/theme/{bundleToOverride}/path/to/twig/file*

For instance, in order to override the default layout provided by the core
bundle, you have to create a template in your bundle at this location:

*Resources/views/theme/ClarolineCoreBundle/layout.html.twig*

[[Documentation index]][index_path]

[index_path]: ../../index.md
