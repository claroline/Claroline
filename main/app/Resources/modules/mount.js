/* eslint-disable no-unused-vars, no-global-assign */
/* global __webpack_public_path__ */

import React from 'react'
import ReactDOM from 'react-dom'
import {Provider} from 'react-redux'

// todo : find where I must put it
// I put it here for now because it's the root of all apps
import {asset} from '#/main/core/scaffolding/asset'

__webpack_public_path__ = asset('dist/')

import {createStore} from '#/main/core/scaffolding/store'
import {combineReducers} from '#/main/core/scaffolding/reducer'

/**
 * Mounts a new React/Redux app into the DOM.
 *
 * @param {HTMLElement}     container     - the HTML element which will hold the JS app.
 * @param {mixed}           rootComponent - the React root component of the app.
 * @param {object|function} reducers      - an object containing the reducers of the app.
 * @param {object}          initialData   - the data to preload in store on app mount.
 */
function mount(container, rootComponent, reducers = null, initialData = {}) {
  let appRoot
  if (reducers) {
    // Create store
    const store = createStore(
      // register reducer
      typeof reducers === 'function' ? reducers : combineReducers(reducers),
      // register initial state
      initialData
    )

    appRoot = React.createElement(
      Provider, {
        store: store
      },
      React.createElement(rootComponent)
    )
  } else {
    appRoot = React.createElement(rootComponent)
  }

  // Render app
  try {
    ReactDOM.render(appRoot, container)
  } catch (error) {
    // rethrow errors (in some case they are swallowed)
    throw error
  }
}

function unmount(container) {
  ReactDOM.unmountComponentAtNode(container)
}

export {
  mount,
  unmount
}
