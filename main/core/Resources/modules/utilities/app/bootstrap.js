import React from 'react'
import ReactDOM from 'react-dom'
import {Provider} from 'react-redux'

import {combineReducers, createStore} from '#/main/core/utilities/redux'

/**
 * Bootstraps a new React/Redux app.
 *
 * @param {string}   containerSelector - a selector to retrieve the HTML element which will hold the JS app.
 * @param {mixed}    rootComponent     - the React root component of the app.
 * @param {object}   reducers          - an object containing the reducers of the app.
 * @param {function} transformData     - a function to transform data before adding them to the store.
 */
export function bootstrap(containerSelector, rootComponent, reducers, transformData = (data) => data) {
  // Retrieve app container
  const container = document.querySelector(containerSelector)
  if (!container) {
    throw new Error(`Container "${containerSelector}" for app can not be found.`)
  }

  // Get initial data from container data attributes
  const initialData = {}
  if (container.dataset) {
    for (let prop in container.dataset) {
      if (container.dataset.hasOwnProperty(prop) && 0 < container.dataset[prop].length) {
        initialData[prop] = JSON.parse(container.dataset[prop])
      }
    }
  }

  // Render app
  ReactDOM.render(
    React.createElement(
      Provider,
      {
        store: createStore(combineReducers(reducers), transformData(initialData))
      },
      React.createElement(rootComponent)
    ),
    container
  )
}
