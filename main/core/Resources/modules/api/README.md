# API module

## Middleware

The api middleware is highly inspired by:
(Redux real world example)[https://github.com/reactjs/redux/blob/master/examples/real-world/src/middleware/api.js].

It permits to declare new actions that will be caught and transformed in API request.

### Requirements

- Requires to be registered in the app store.
- Requires `redux-thunk` to dispatch the correct sets of actions on AJAX events.
- As it needs `redux-thunk`, the api middleware must to be registered **before** it.

### Usage
Managed action example:

```
import {REQUEST_SEND} from '[path_to_module]/api/actions'

// ...

actions.fetchAttempt = quizId => ({
  [REQUEST_SEND]: {
    route: ['exercise_attempt_start', {exerciseId: quizId}],
    request: {method: 'POST'},
    success: (data, dispatch) => {
      const normalized = normalize(data)
      return dispatch(actions.initPlayer(normalized.paper, normalized.answers))
    },
    failure: () => navigate('overview')
  }
})
```

Action parameters:
- `route (array)`: the route definition of the api endpoint. It's passed to FOSJsRouting to generate the final URL.
 The first param is the route name, the second it's an arguments object.
- `url (string)`: the url to call. If provided, it's used in priority, if not, the middleware will fallback to the `route` param.
- `request (object|Request)`: a custom request to send. See (Fetch)[https://developer.mozilla.org/en-US/docs/Web/API/GlobalFetch/fetch] for more detail..
- `before (func)`:  a callback to execute before sending the request
                    (called with dispatch function)
- `success (func)`: a callback to execute AJAX request is processed without errors
                    (called with response data and dispatch function)
- `failure (func)`: a callback to execute if something goes wrong
                    (called with error object and dispatch function)
Action only requires a `route` or `url` parameter. All other ones are optional.
If not set in the `request`, the middleware will make `GET` requests by default.

# Enhancements
- The error handler should not only manage HTTP errors.
- The error handler should give access to the detail of the error.
- The middleware should handle offline mode.
