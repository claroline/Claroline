---
layout: default
title: Parameters
---

# Parameters

> This section only explain how to retrieve platform parameters in the UI.
> To know how to manage and expose parameters in the API, please see Application > Parameters

There are two methods to access parameters

## From the Redux store

```js
import {selectors as configSelectors} from '#/main/app/config/store'

configSelectors.param(state, 'my_parameter')
configSelectors.param(state, 'my_parameter.key')

```

# With the `param` utility

```js
import {param} from '#/main/app/config'

param('my_parameter')
param('my_parameter.key')

```

> You SHOULD always try to get the parameters from the redux store,
> only use the `param()` utility if you don't have access to a redux store in your app (eg. data validators)
