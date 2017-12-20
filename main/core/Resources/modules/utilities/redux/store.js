/* global process, require, window */

import {
  applyMiddleware,
  compose,
  createStore as baseCreate
} from 'redux'
import thunk from 'redux-thunk'

import {apiMiddleware} from '#/main/core/api/middleware'

const middleware = [apiMiddleware, thunk]

// pre-configure store for all redux apps
if (process.env.NODE_ENV !== 'production') {
  const freeze = require('redux-freeze')
  middleware.push(freeze)
}

function createStore(reducers, initialState, enhancers = []) {
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

export {
  createStore
}
