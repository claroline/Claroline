/* global process, require */

import {
  applyMiddleware,
  combineReducers,
  compose,
  createStore as baseCreate
} from 'redux'
import thunk from 'redux-thunk'
import invariant from 'invariant'
import {apiMiddleware} from '#/main/core/api/middleware'

const middleware = [apiMiddleware, thunk]

export {combineReducers}

// generator for very simple action creators (see redux doc)
export function makeActionCreator(type, ...argNames) {
  return (...args) => {
    let action = { type }
    argNames.forEach((arg, index) => {
      invariant(args[index] !== undefined, `${argNames[index]} is required`)
      action[argNames[index]] = args[index]
    })
    return action
  }
}

export function makeReducer(initialState, handlers) {
  return function reducer(state = initialState, action) {
    if (handlers.hasOwnProperty(action.type)) {
      return handlers[action.type](state, action)
    } else {
      return state
    }
  }
}

if (process.env.NODE_ENV !== 'production') {
  const freeze = require('redux-freeze')
  middleware.push(freeze)
}

export function createStore(reducers, initialState, enhancers = []) {
  // Add dev tools
  if (process.env.NODE_ENV !== 'production') {
    // Register browser extension
    if (window.devToolsExtension) {
      enhancers.push(window.devToolsExtension())
    }
  }

  return baseCreate(
    reducers,
    initialState,
    compose(
      applyMiddleware(...middleware),
      ...enhancers
    )
  )
}
