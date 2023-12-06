import {createElement} from 'react'
import {render, unmountComponentAtNode} from 'react-dom'

// todo : find where I must put it
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
import {getApps} from '#/main/app/plugins'

/**
 * Mounts a new React/Redux app into the DOM.
 *
 * @param {HTMLElement} container     - the HTML element which will hold the JS app.
 * @param {*}           rootComponent - the React root component of the app.
 * @param {object}      reducers      - an object containing the reducers of the app.
 * @param {object}      initialData   - the data to preload in store on app mount.
 * @param {boolean}     embedded      - is the mounted app is mounted into another ?
 * @param {string}      defaultPath   - the path to match when mounting the router.
 */
function mount(
  container,
  rootComponent,
  reducers = {},
  initialData = {},
  embedded = false,
  defaultPath = ''
) {
  // append plugin reducers
  const pluginStores = getApps('store') || {}

  Promise.all(
    Object.keys(pluginStores).map(pluginStore => pluginStores[pluginStore]())
  ).then((loadedStores) => {
    loadedStores.map(storeModule => {
      // TODO : add some checks
      reducers[storeModule.selectors.STORE_NAME] = storeModule.reducer
    })

    // create store
    // we initialize a new store even if the mounted app does not declare reducers
    // we have dynamic reducers which can be added during runtime and they will be fucked up
    // if they don't find a store to use.
    const store = createStore(rootComponent.displayName, reducers, initialData)

    const appRoot = createElement(
      Main, {
        store: store,
        embedded: embedded,
        defaultPath: defaultPath
      },
      createElement(rootComponent)
    )

    // Render app
    try {
      render(appRoot, container)
    } catch (error) {
      // rethrow errors (in some case they are swallowed)
      throw error
    }
  })
}

function unmount(container) {
  unmountComponentAtNode(container)
}

export {
  mount,
  unmount
}
