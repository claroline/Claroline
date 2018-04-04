import React from 'react'
import ReactDOM from 'react-dom'
import {Provider} from 'react-redux'
import invariant from 'invariant'

// todo : find where I must put it
// I put it here for now because it's the root of all apps
import {asset} from '#/main/core/scaffolding/asset'
__webpack_public_path__ = asset('dist/')

import {createStore} from '#/main/core/scaffolding/store'
import {combineReducers} from '#/main/core/scaffolding/reducer'

function getContainer(selector) {
  const container = document.querySelector(selector)
  if (!container) {
    throw new Error(`Container "${selector}" for app can not be found.`)
  }

  return container
}

function getInitialData(container) {
  const initialData = {}
  if (container.dataset) {
    for (let prop in container.dataset) {
      if (container.dataset.hasOwnProperty(prop) && 0 < container.dataset[prop].length) {
        initialData[prop] = JSON.parse(container.dataset[prop])
      }
    }
  }

  return initialData
}

/**
 * Bootstraps a new React/Redux app.
 *
 * @param {string}          containerSelector - a selector to retrieve the HTML element which will hold the JS app.
 * @param {mixed}           rootComponent     - the React root component of the app.
 * @param {object|function} reducers          - an object containing the reducers of the app.
 * @param {function}        transformData     - a function to transform data before adding them to the store.
 */
function bootstrap(containerSelector, rootComponent, reducers = null, transformData = (data) => data) {
  // Retrieve app container
  const container = getContainer(containerSelector)

  // Get initial data from container data attributes
  const initialData = getInitialData(container)

  let appRoot
  if (reducers) {
    // Create store
    const store = createStore(
      // register reducer
      typeof reducers === 'function' ? reducers : combineReducers(reducers),
      // register initial state
      transformData(initialData)
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
    console.log(error)
    throw error
  }
}

export {
  bootstrap
}
