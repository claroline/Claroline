import {createElement} from 'react'
import {createRoot} from 'react-dom/client'
import merge from 'lodash/merge'

// (I put it here for now because it's the root of all apps)
// this give the source paths to webpack for dynamic loading
import {asset, env} from '#/main/app/config'

/* eslint-disable no-undef, no-unused-vars, no-global-assign */
if ('development' === env()) {
  __webpack_public_path__ = 'http://localhost:8080/dist/'
} else {
  __webpack_public_path__ = asset('dist/')
}
/* eslint-enable no-undef, no-unused-vars, no-global-assign */

import {createStore} from '#/main/app/store'
import {Main} from '#/main/app/components/main'
import {selectors as apiSelectors, reducer as apiReducer} from '#/main/app/api/store'
import {selectors as configSelectors, reducer as configReducer} from '#/main/app/config/store'
import {selectors as securitySelectors, reducer as securityReducer} from '#/main/app/security/store'

/**
 * Mounts a new React/Redux app into the DOM.
 *
 * @param {HTMLElement} container         - the HTML element which will hold the JS app.
 * @param {*}           rootComponent     - the React root component of the app.
 * @param {object}      reducers          - an object containing the reducers of the app.
 * @param {object}      initialData       - the data to preload in store on app mount.
 * @param {boolean}     embedded          - is the mounted app is mounted into another ?
 * @param {string}      defaultPath       - the path to match when mounting the router.
 * @param {array}       customMiddlewares - a list of custom middlewares to append to the store (will be added to the default ones)
 */
function mount(
  container,
  rootComponent,
  reducers = {},
  initialData = {},
  embedded = false,
  defaultPath = '',
  customMiddlewares= []
) {
  // create store
  const store = createStore(rootComponent.displayName, merge({
    [apiSelectors.STORE_NAME]: apiReducer,
    [configSelectors.STORE_NAME]: configReducer,
    [securitySelectors.STORE_NAME]: securityReducer
  }, reducers), initialData, customMiddlewares)

  const appRoot = createElement(
    Main, {
      store: store,
      embedded: embedded,
      defaultPath: defaultPath
    },
    createElement(rootComponent)
  )

  // render app
  const root = createRoot(container)
  root.render(
    createElement(
      Main, {
        store: store,
        embedded: embedded,
        defaultPath: defaultPath
      },
      createElement(rootComponent)
    )
  )

  return root
}

function unmount(app) {
  app.unmount()
}

export {
  mount,
  unmount
}
