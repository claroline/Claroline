---
layout: default
title: API
---

# API

The API module is responsible of the UI calls to the underlying data api.

> To learn more about creating an API for your user interface, please check [API documentation](/sections/api/index).

## Router

We use [FOSJsRouting](https://github.com/FriendsOfSymfony/FOSJsRoutingBundle) to expose API routes to the UI.

> ATTENTION : Only routes marked with the `expose: true` flag in the symfony routing configuration
> are made available to the UI.

### Usage

```js

import {url} from '#/main/app/api'

// route without params
const userListUrl = url(['apiv2_user_list'])
console.log(userListUrl) // print : /apiv2/user

// route with params
const userGetUrl = url(['apiv2_user_get', {id: '123'}])
console.log(userGetUrl) // print : /apiv2/user/123
```

## Middleware

The api middleware is highly inspired by:
[Redux real world example](https://github.com/reactjs/redux/blob/master/examples/real-world/src/middleware/api.js).

It permits to declare new actions that will be caught and transformed in API request.

### Requirements

- Requires to be registered in the app store.
- Requires `redux-thunk` to dispatch the correct sets of actions on AJAX events.
- As it needs `redux-thunk`, the api middleware must to be registered **before** it.

### Usage

Managed action example:

```js
import {API_REQUEST} from '#/main/app/api'

// ...

// Simple API request
actions.fetchAttempt = (quizId) => ({
  [API_REQUEST]: {
    url: ['exercise_attempt_start', {exerciseId: quizId}],
    request: {method: 'POST'},
    success: (data) => doSometing(),
    error: () => doSometingOnError()
  }
})

// An API request which need to re-dispatch a redux action on completion
// (for example, for storing the result of the request in the redux store)
actions.fetchAttempt = (quizId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['exercise_attempt_start', {exerciseId: quizId}],
    request: {method: 'POST'},
    success: (data) => {
      const normalized = normalize(data)
      
      // dispatch a new redux action
      return dispatch(actions.initPlayer(normalized.paper, normalized.answers))
    },
    error: () => doSometing()
  }
})
```

Action parameters:
- `url (array)`: the route definition of the api endpoint. It's passed to FOSJsRouting to generate the final URL.
 The first param is the route name, the second it's an arguments object.
- `url (string)`: the url to call. If provided, it's used in priority, if not, the middleware will fallback to the `route` param.
- `request (object|Request)`: a custom request to send. See [Fetch](https://developer.mozilla.org/en-US/docs/Web/API/GlobalFetch/fetch) for more detail.
- `before (func)`:  a callback to execute before sending the request
- `success (func)`: a callback to execute AJAX request is processed without errors (called with response data)
- `error (func)`: a callback to execute if something goes wrong (called with error object)

Action only requires a `route` or `url` parameter. All other ones are optional.
If not set in the `request`, the middleware will make `GET` requests by default.
