import {mount} from '#/main/app/mount'

/**
 * Retrieves app container with CSS selector.
 * NB. It MUST be any selector understandable by `querySelector`.
 *
 * @param selector
 * @return {Element}
 */
function getContainer(selector) {
  const container = document.querySelector(selector)
  if (!container) {
    throw new Error(`Container "${selector}" for app can not be found.`)
  }

  return container
}

/**
 * Retrieves data passed to the app via container data attributes.
 *
 * @param container
 * @return {{}}
 */
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
  // retrieve app container
  const container = getContainer(containerSelector)

  // get initial data from container data attributes
  const initialData = getInitialData(container)

  // mount the application
  mount(container, rootComponent, reducers, transformData(initialData))
}

export {
  bootstrap
}
