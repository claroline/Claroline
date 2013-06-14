[[Documentation index]][index_path]

# Themes documentation #

- [Introduction](#introduction)
- [Create a new theme](#newtheme)
- [Compile a theme](#compiletheme)
- [Overwrite Claroline twig files](#overwrite)
- [Generate themes](#generate)
- [References and External links](#references)

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

This twig file defines a [asset stylesheets][assets] tag with the informations of your *css* or *less* file. You can define also a filter as *lessphp* and the path of output file that will be compiled in a single file and store in public *web* folder of **Symfony2** framework.

*ExampleBundle/Resources/views/less/example/theme.html.twig*

    {% stylesheets
        debug=false
        filter="lessphp"
        output="bundles/clarolinecore/css/themes/examplebundle-theme/bootstrap.css"
        "@ClarolineExampleBundle/Resources/views/less/example/common.less"
    %}
        <link href="{{ asset_url }}" rel="stylesheet" media="screen">
    {% endstylesheets %}

In this example `@ClarolineExampleBundle/Resources/views/less/example/common.less` is the path of a common file that contains imports of your custom theme files, but also the import of important **Bootstrap** files.

You can find an example of this common file in our [ExampleBundle][examplebundle].

In this example there is two more important files for our theme.

- A *variable.less* in order to define all **Bootstrap** variables that you want to overwrite.
- A *theme.less* in order to add additional style rules.

*ExampleBundle/Resources/views/less/example/variable.less*

    @navbarInverseLinkColor: #ccc;
    @linkColor: darken(@orange, 5%);
    @baseBorderRadius: 8px

At this point you can use **Less CSS** and **Bootstrap** function or variables, as `darken`, `lighten` or `@orange`.
[Here][lesscssfunction] is a function reference of **Less CSS**.

*ExampleBundle/Resources/views/less/example/theme.less*

    body
    {
        background:@infoBackground;
    }

    .brand
    {
        background:transparent url(../../../images/logo/claroline-logo-bulles-22x25.png) 20px 5px no-repeat;
        padding-left:50px !important;

    }

In this example the path of images in the less file are relatives of the compiled output file, if you want to change this you can use a [cssrewrite][assets] filter.

## <a id="compiletheme"></a>Compile a theme ##

**Less CSS** is used to be compiled with [NodeJS][nodejs] but we chose **LessPHP** in order to compile themes from the web in **PHP** servers of shared hosting providers that have not support of **NodeJS**.

In order to compile your new theme you need simply to install your plugin in the **Claroline** platform, then you can chose your new theme in the platform parameters in administration section.

If your plugin is already installed and you want to compile modifications of your theme, you can run `php app/console assetic:dump`, this **Symfony2** command will compile all the themes but not only of your plugin but the themes of all the bundles and will install too all the assets.

If you want to compile only one theme of your plugin, you can use our `claroline:theme:compile` command with the *path* or *name* of your theme as following:

`php app/console claroline:theme:compile "ExampleBundle Theme"`

`php app/console claroline:theme:compile ClarolineExampleBundle:less:example/theme.html.twig`

Note that if the *name* of your theme have spaces you must to use quotes.

If you use the command with the *path*, you need to write it with the *":"* separator of **Symfony2** convention.

If the *name* or *path* does not exists or you do not specify it, all the themes will be compiled.

## <a id="overwrite"></a>Overwrite Claroline twig files ##

In order to *overwrite* **Claroline** twig files from your plugin, you need first to [create a theme](#newtheme) and then create a folder with the same name of your theme (without spaces and lowercase) in *views*.

In this folder you can create your own twig file replicating the same location of *ClarolineCoreBundle* views folder. The twig file that lives inside the *ClarolineCoreBundle* will be entirely ignored, and your file will be use instead.

You can find an example of that in our [ExampleBundle][examplebundle].

`src/plugin/Claroline/ExampleBundle/Resources/views/examplebundletheme/Layout/top_bar.html.twig`

*overwrites*

`src/core/Claroline/CoreBundle/Resources/views/Layout/top_bar.html.twig`

That only works if your theme is chosen in platform parameters in the administration section.

## <a id="generate"></a>Generate themes ##

We know that for someone who never hear talk about **Less CSS** or **Bootstrap** maybe could be hard to start to write a theme.

For that case we create simple theme generator, you can find it in the link *"Create a new theme"* in platform parameters in administration section. There you can chose the colors to be used in a theme with a simple color picker. In section of *"more option"* you can change more that only colors.

When you finish to customize your theme you can simply save it or preview it, this theme will be compile automatically and can be used instantly in the platform.

If you dont like the results of a theme, you can simply chose it in theme generator panel and click in delete button.

The less files of your generated theme are stored in *src/core/Claroline/CoreBundle/Resources/views/less-generated/*, you can copy that files as template to start a theme in a plugin, you can simply modify the file *theme.less* in order to add additional style rules, you can consider that file as a simple CSS file but you can use at any place **Less CSS** or **Bootstrap** functions and variables.

## <a id="references"></a>References and External links ##

- [Claroline][claroline]
- [Bootstrap][bootstrap]
- [Less CSS][lesscss]
- [Less CSS Functions][lesscssfunction]
- [LessPHP][lessphp]
- [Sass][sass]
- [Foundation][foundation]
- [Assetic documentation][assets]
- [ExampleBundle][examplebundle]
- [NodeJS][nodejs]

[[Documentation index]][index_path]


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
[examplebundle]: https://github.com/claroline/ExampleBundle
[lesscssfunction]: http://lesscss.org/#reference
[nodejs]: http://nodejs.org/
