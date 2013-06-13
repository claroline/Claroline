[[Documentation index]][index_path]

# Themes documentation #

- [Introduction](#introduction)
- [Create a new theme](#newtheme)
- [Compile a theme](#compiletheme)
- [Overwrite Claroline twig files](#overwrite)
- [Generate themes](#generate)
- [References](#references)
- [External links](#external)

## <a id="introduction"></a>Introduction ##

In [Claroline][claroline] we use [Bootstrap][bootstrap], [LESS CSS][lesscss] and [LessPHP][lessphp] in order to create, compile and generate our themes.

- **Bootstrap** is a CSS Framework created at [Twitter][twitter] by [@mdo][mdo] and [@fat][fat].
- **Less CSS** is a dynamic stylesheet programming language influenced by [Sass][sass].
- **LessPHP** is a compiler for LESS written in PHP.

Our themes and **Bootstrap** are written in **Less CSS** but you can create a theme in flat CSS, Sass, SCSS or with another CSS framework as [Foundation][foundation], but this documentation will be only focused in **Bootstrap** and **Less CSS**.

## <a id="newtheme"></a>Create a new theme ##

In order to create a new theme in a **Claroline Plugin** you must first to define his *name* and *path* in the **config.yml** of the plugin.

*ExampleBundle/Resources/config/config.yml*

    themes:
      - name: "ExampleBundle Theme"
        path: less/example/theme.html.twig

The *path* of the theme is in fact a path to a twig file inside the views folder of you plugin (ExampleBundle/Resources/views).

This twig file defines a [asset stylesheets][assets] tag with the informations of your *css* or *less* file. You can define also a filter as *lessphp* and the path of the compile output file that will be store in *web* folder of Symfony2 framework.

*ExampleBundle/Resources/views/less/example/theme.html.twig*

    {% stylesheets
        debug=false
        filter="lessphp"
        output="bundles/clarolinecore/css/themes/examplebundle-theme/bootstrap.css"
        "@ClarolineExampleBundle/Resources/views/less/example/common.less"
    %}
        <link href="{{ asset_url }}" rel="stylesheet" media="screen">
    {% endstylesheets %}

In this example `@ClarolineExampleBundle/Resources/views/less/example/common.less` is the path of a common file that contains imports of your custom theme, but also the import of important bootstrap files.

## <a id="compiletheme"></a>Compile a theme ##

## <a id="overwrite"></a>Overwrite Claroline twig files ##

## <a id="generate"></a>Generate themes ##


## <a id="references"></a>References ##

## <a id="external"></a>External links ##




[index_path]: ../index.md
[claroline]: http://www.claroline.net
[bootstrap]: http://twitter.github.io/bootstrap/
[lesscss]: http://lesscss.org/
[lessphp]: http://leafo.net/lessphp/
[twitter]: https://twitter.com
[mdo]: https://twitter.com/mdo
[fat]: https://twitter.com/fat
[sass]: http://sass-lang.com/
[foundation]: http://foundation.zurb.com/
[assets]: http://symfony.com/doc/current/cookbook/assetic/asset_management.html
