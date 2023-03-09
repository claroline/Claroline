---
layout: default
title: Localization
---

# Localization

## Translations

Symfony translations are made available through [BazingaJsTranslationBundle](https://github.com/willdurand/BazingaJsTranslationBundle).

The library will transform the JSON translations defined in `MY_PLUGIN/Resources/translations` when you run
the command :

```shell
$ php bin/console bazing:js-translation:dump public/js
```

> You may need to empty the symfony cache and your browser cache to see the changes.

You can now access the translations with the following code in JS : 

```js
    import {trans, transChoice} from '#/main/app/intl/translation'

    trans(translationKey, placeholders = {}, domain = 'platform')
    transChoice(translationKey, count, placeholders = {}, domain = 'platform')
```

## Dates

Dates are managed by [MomentJS](https://momentjs.com/).
