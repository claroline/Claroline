import {mount} from '#/main/app/dom/mount'

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
      if (container.dataset[prop] && 0 < container.dataset[prop].length) {
        initialData[prop] = JSON.parse(container.dataset[prop])
      }
    }
  }

  return initialData
}

/**
 * Bootstraps a new React/Redux app.
 *
 * @param {string}   containerSelector - a selector to retrieve the HTML element which will hold the JS app.
 * @param {*}        rootComponent     - the React root component of the app.
 * @param {object}   reducers          - an object containing the reducers of the app.
 * @param {function} transformData     - a function to transform data before adding them to the store.
 * @param {string}   defaultPath       - the path to match when mounting the router.
 * @param {array}    customMiddlewares - a list of custom middlewares to append to the store (will be added to the default ones)
 */
function bootstrap(
  containerSelector,
  rootComponent,
  reducers = null,
  transformData = (data) => data,
  defaultPath = '',
  customMiddlewares = []
) {
  // retrieve app container
  const container = getContainer(containerSelector)

  // get initial data from container data attributes
  const initialData = getInitialData(container)

  // mount the application
  return mount(container, rootComponent, reducers, transformData(initialData), false, defaultPath, customMiddlewares)
}

export {
  bootstrap
}
