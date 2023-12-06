/* global require, window */

import {
  applyMiddleware,
  compose,
  createStore as baseCreate
} from 'redux'
import thunk from 'redux-thunk'
import get from 'lodash/get'
import merge from 'lodash/merge'
import set from 'lodash/set'

import {env} from '#/main/app/config'
import {combineReducers} from '#/main/app/store/reducer'

import {apiMiddleware} from '#/main/app/api/store/middleware'

// pre-configure store for all redux apps
const middleware = [apiMiddleware, thunk]

// add dev tools
if ('production' !== env()) {
  // Register redux freeze (it will throw errors if the state is mistakenly mutated)
  middleware.push(
    require('redux-freeze')
  )
}

/**
 * Generates a new pre-configured application store.
 *
 * @param {string} name              - the name of the store
 * @param {object} reducers          - an object containing a list of reducers to mount in the store.
 * @param {object} initialState      - the data to preload in the store at creation.
 * @param {array}  customMiddlewares - a list of custom middlewares to append to the store (will be added to the default ones)
 *
 * @return {*}
 */
function createStore(name, reducers, initialState = {}, customMiddlewares = []) {
  // preserve initial state for not-yet-loaded reducers
  const createReducer = (reducers) => {
    const reducerNames = Object.keys(reducers)
    Object.keys(initialState).forEach(item => {
      if (reducerNames.indexOf(item) === -1) {
        reducers[item] = (state = initialState[item]) => state
      }
    })

    return combineReducers(reducers)
  }

  // register browser extension
  // we must do it at each store creation in order to register all
  // of them in the dev console
  const composeEnhancers =
    env() !== 'production' &&
    typeof window === 'object' &&
    window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ ?
      window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({
        name: name,
        // this is required by dynamic reducer
        // without it, all actions stack is replayed at each reducer injection
        shouldHotReload: false
      }) : compose

  const store = baseCreate(
    createReducer(reducers),
    initialState,
    composeEnhancers(
      applyMiddleware(...merge([], middleware, customMiddlewares))
    )
  )

  // support for dynamic reducer loading
  store.asyncReducers = {}
  store.injectReducer = (key, reducer) => {
    if (!get(store.asyncReducers, key, false)) {
      // only append non mounted reducers
      set(store.asyncReducers, key, reducer)

      store.replaceReducer(
        createReducer(merge({}, reducers, store.asyncReducers))
      )
    }

    return store
  }

  return store
}

export {
  createStore
}
