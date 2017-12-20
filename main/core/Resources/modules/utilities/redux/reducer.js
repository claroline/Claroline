import {combineReducers} from 'redux'

// syntax sugar to avoid writing reducers as big switches.
// Example :
//   makeReducer([], {
//     [LIST_RESET_SELECT]: resetSelect,
//     [LIST_TOGGLE_SELECT]: toggleSelect,
//     [LIST_TOGGLE_SELECT_ALL]: toggleSelectAll
//   })
function makeReducer(initialState, handlers) {
  return (state = initialState, action) => {
    if (handlers.hasOwnProperty(action.type)) {
      return handlers[action.type](state, action)
    }

    return state
  }
}

// [Advanced use]
// make actions only trigger reducers for the current instance
// without this, actions are caught by all instances of the reducer in the app
function makeInstanceReducer(initialState, handlers) {
  return (instanceName, instanceInitialState = null) => {
    const instanceHandlers = {}
    for (let actionName in handlers) {
      if (handlers.hasOwnProperty(actionName)) {
        instanceHandlers[actionName+'/'+instanceName] = handlers[actionName]
      }
    }

    return makeReducer(instanceInitialState || initialState, instanceHandlers)
  }
}

// [Advanced use]
// applies 2 or more reducers to the same store key.
// NB. This is low level API to apply custom reducers to base app components.
//     If you end up using it, you may be doing bad things !
function reduceReducers(...reducers) {
  return (previous, current) =>
    reducers.reduce(
      (p, r) => r(p, current),
      previous
    )
}

export {
  combineReducers, // reexported from redux
  makeReducer,
  makeInstanceReducer,
  reduceReducers
}
