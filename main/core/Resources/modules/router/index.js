import React from 'react'
import {NavLink, Switch, Redirect, withRouter, matchPath} from 'react-router-dom'

import {history} from '#/main/core/router/history'
import {Router, Routes, Route} from '#/main/core/router/components/router.jsx'

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
 *
 * @deprecated use the RoutedPage component instead.
 */
function routedApp(routesConfig, basePath = '') {
  return () => {
    const RoutedApp = React.createElement(
      Router,
      {},
      React.createElement(Routes, {
        basePath: basePath,
        routes: routesConfig
      })
    )

    return RoutedApp
  }
}

/**
 * Shortcut to navigate using the correct history.
 */
function navigate(url) {
  history.push(url)
}

export {
  Router,
  Routes,
  Route,
  Redirect,
  NavLink,
  Switch,
  routedApp,
  navigate,
  matchPath,
  withRouter
}
