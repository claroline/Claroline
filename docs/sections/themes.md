---
layout: default
title: Themes
---

# Themes

Claroline Themes are written in [LESS](https://lesscss.org/) and are built on top of [Twitter Bootstrap 3](https://getbootstrap.com/docs/3.3)
and [FontAwesome](https://fontawesome.com/).

The Claroline Connect application comes with some default themes ready to be used.
It's also possible to create custom themes to fit your needs.

A theme is made up of 2 elements :
- A LESS application which will be compiled into CSS.
- An entity `Claroline\ThemeBundle\Entity\Theme` which will be used to declare the theme to the Claroline application.

## File structure

The base styles of the application are declared in `MY_PROJECT_DIR/src/main/app/Resources/styles`.

It includes :
- Twitter Bootstrap styles
- FontAwesome (free) icons
- Styles for the Claroline components
- A set of LESS variables for customization

## Create a custom theme

You can either create a single `MY_THEME_NAME.less` file
or create a `MY_THEME_NAME` directory which contains at least a `index.less`

Themes **MUST** include the default Claroline styles.
You'll need to include them in your theme file :

```less
// Load default theme skeleton
@import "/src/main/app/Resources/styles/index";
```

There are 2 ways to declare new themes :

### From source files

#### 1. Create your theme files

Source files for custom themes are stored in `MY_PROJECT_DIR/files/themes-src`.

#### 2. Declare the theme to the Claroline Application

```
$ php bin/console claroline:theme:create MY_THEME_NAME
```

This will create a `Claroline\ThemeBundle\Entity\Theme` entity and make it available to be used
in your platform configuration.

### From a plugin

Plugins can introduce platform themes. Once a plugin is installed, the themes
it provides become available in the *Appearance* section of the general
parameters of the platform.

#### 1. Create your theme files

Source files for custom themes are stored in `MY_PLUGIN_DIR/Resources/themes`.

#### 2. Declare the theme to the Claroline Application

Plugin themes are declared in the `themes` section of the plugin configuration file:

```yml
plugin:
  # ...
  themes:
    - name: "Custom Theme"
```

The `Claroline\ThemeBundle\Entity\Theme` entity will be automatically created when the plugin is installed/updated.

### Best practices

You **MUST** include the base styles of the Claroline application.

You **MUST NOT** override `@zindex-` variables.
Those are low-level variables and are not meant to be customized by themes.

You **SHOULD NOT** declare custom rules.
Custom rules can be broken when we change the structure of the DOM and are hard to maintain.

**Rebuild themes which are installed in the platform**

``php bin/console claroline:theme:build [options]``

#### Rebuild only the theme currently used by the platform :

``php bin/console claroline:theme:build --current``

or

``php bin/console claroline:theme:build -c``

#### Rebuild only this theme :

``php bin/console claroline:theme:build --theme[=THEME]``

or

``php bin/console claroline:theme:build -t[=THEME]``

#### Rebuild themes without using cache :

``php bin/console claroline:theme:build --no-cache``

or

``php bin/console claroline:theme:build -nc``