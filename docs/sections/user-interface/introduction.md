---
layout: default
title: Introduction
---

# Introduction

> **Note for the readers**
>
> As the whole Claroline interface is built on top of [React](https://reactjs.org) and [Redux](https://redux.js.org),
> we assume you know their basic concepts and code syntax.
>
> If you don't, you should read their documentation first :
> - The [Quick start](https://reactjs.org/docs/hello-world.html) of the React documentation.
> - The [Introduction](https://redux.js.org/introduction) of the Redux documentation.
>
> To a lesser extent, some knowledge about :
>
> - [ES6 syntax](http://es6-features.org) 
> - [immutability](https://en.wikipedia.org/wiki/Immutable_object)
> - [functional programming](https://en.wikipedia.org/wiki/Functional_programming)
> - [webpack](https://webpack.js.org/)
> - [lodash](https://lodash.com) library
> - [reselect](https://github.com/reactjs/reselect#reselect) library
>
> will help to understand the current document.

## Webpack

All the JavaScript needed by the application is compiled and distributed by webpack.
To ease the development process, we use the `webpack-dev-server` in order to serve the files.
This requires to run the dev server in command line :

    npm run webpack:dev

## Directory structure

JavaScript applications for plugins are stored in `MY_PLUGIN/Resources/modules`.

## Import

Webpack defines some aliases for imports in order to simplify the path to the JS source files

### Alias for the Claroline Connect distribution plugins

*Plugins declared by Claroline Connect in the `src` directory.*

#### Original path
```
/src/main/app/Resources/modules/page/components/full.jsx
```

#### Alias
```
#/main/app/page/components/full
```

#### Usage
```js
import {PageFull} from '#/main/app/page/components/full'
```

### Alias for external plugins

*Plugins declared by distribution packages located in the `vendor` directory.*

#### Original path
```
/vendor/vendor-name/package-name/plugin/MY_PLUGIN/Resources/modules/tools/my-tool/index.js
```

#### Alias
```
~/vendor-name/package-name/MY_PLUGIN/tools/my-tool
```

#### Usage
```js
import {PageFull} from '#/main/app/page/components/full'
```
