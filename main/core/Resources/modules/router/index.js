import React from 'react'

import {RouterContainer} from '#/main/core/router/containers/router.jsx'

/**
 * Creates react router components based on config.
 *
 * NB:
 *   if you use connected components that needs to access route params,
 *   you have to tell them there is a router.
 *   @see https://reacttraining.com/react-router/web/guides/redux-integration
 *
 * Example of simple routing config :
 *   [
 *     {path: '',     component: MyComponent, exact: true},
 *     {path: '/:id', component: MyOtherComponent}
 *   ]
 *
 * Example of nested routing config :
 *   [{
 *     path: '/main',
 *     routes: [
 *       {path: '',     component: MyComponent, exact: true},
 *       {path: '/:id', component: MyOtherComponent}
 *     ]
 *   }]
 *
 * @param {Array}  routesConfig
 * @param {string} basePath
 */
function routedApp(routesConfig, basePath = '') {
  return () => {
    const RoutedApp = React.createElement(RouterContainer, {
      basePath: basePath,
      routes: routesConfig
    })

    return RoutedApp
  }
}

export {
  routedApp
}
