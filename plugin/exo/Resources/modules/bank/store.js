/* global process, require */

import {
  applyMiddleware,
  compose,
  createStore as baseCreate
} from 'redux'
import thunk from 'redux-thunk'
import {apiMiddleware} from './../api/middleware'
import {bankApp} from './reducers/index'

const middleware = [apiMiddleware, thunk]

if (process.env.NODE_ENV !== 'production') {
  const freeze = require('redux-freeze')
  middleware.push(freeze)
}

export function createStore(initialState) {
  return baseCreate(
    bankApp,
    initialState,
    compose(
      applyMiddleware(...middleware),
      window.devToolsExtension ? window.devToolsExtension() : f => f
    )
  )
}
