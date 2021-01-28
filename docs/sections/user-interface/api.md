---
layout: default
title: API
---

# API

The API module is responsible of the UI calls to the underlying data api.

> To learn more about creating an API for your user interface, please check [API documentation](/sections/api/index).

## Router

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

```
import {API_REQUEST} from '#/main/core/api/actions'

// ...

actions.fetchAttempt = quizId => ({
  [API_REQUEST]: {
    url: ['exercise_attempt_start', {exerciseId: quizId}],
    request: {method: 'POST'},
    success: (data, dispatch) => {
      const normalized = normalize(data)
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
                    (called with dispatch function)
- `success (func)`: a callback to execute AJAX request is processed without errors
                    (called with response data and dispatch function)
- `error (func)`: a callback to execute if something goes wrong
                    (called with error object and dispatch function)
Action only requires a `route` or `url` parameter. All other ones are optional.
If not set in the `request`, the middleware will make `GET` requests by default.

## Enhancements
- The error handler should give access to the detail of the error.
- The middleware should handle offline mode.
