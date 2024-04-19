---
layout: default
title: Best practices
---

# Best practices

- [PSR 1](https://www.php-fig.org/psr/psr-1/)
- [PSR 2](https://www.php-fig.org/psr/psr-2/)
- [PSR 4](https://www.php-fig.org/psr/psr-4/)
- [PSR 12](https://www.php-fig.org/psr/psr-12/)
- [Symfony coding standards](http://symfony.com/doc/current/contributing/code/standards.html)
- [JavaScript code conventions](https://www.crockford.com/code.html)

## PHP

- Use "Class constructor property promotion" whenever possible.


## Styles

- Use [Margin](https://getbootstrap.com/docs/5.3/utilities/spacing/#margin-and-padding) utilities to manage spacing between components
  - Use size `3` for standard spacing between components.
  - Use size `1` (e.g. spacing between buttons in a toolbar) or `2` for lighter spacing INSIDE a component.

- Limit page content size with `.content-sm`, `.content-md`, `.content-lg` :
  - `.content-md` : Forms and Details view
  - `.content-lg` : Two columns views
  - Full width : List views, Custom views (like Agenda or Home)
