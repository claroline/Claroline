import React from 'react'
import ReactDOM from 'react-dom'
import {Provider} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

// todo : find where I must put it
// (I put it here for now because it's the root of all apps)
import {asset} from '#/main/app/config'

/* eslint-disable no-undef, no-unused-vars, no-global-assign */
__webpack_public_path__ = asset('dist/')
/* eslint-enable no-undef, no-unused-vars, no-global-assign */

import {createStore} from '#/main/app/store'

/**
 * Mounts a new React/Redux app into the DOM.
 *
 * @param {HTMLElement} container     - the HTML element which will hold the JS app.
 * @param {*}           rootComponent - the React root component of the app.
 * @param {object}      reducers      - an object containing the reducers of the app.
 * @param {object}      initialData   - the data to preload in store on app mount.
 */
function mount(container, rootComponent, reducers = null, initialData = {}) {
  let appRoot
  if (!isEmpty(reducers)) {
    // Create store
    const store = createStore(reducers, initialData)

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
