import React from 'react'
import {
  hashHistory,
  HashRouter as Router,
  Route,
  Switch
} from 'react-router-dom'

function getRouteComponent(route) {
  // todo validate route config

  const routeProps = {
    key: route.path,
    path: route.path,
    exact: !!route.exact
  }

  if (route.routes) {
    routeProps.children = React.createElement(Switch, {}, route.routes.map(routeConfig => getRouteComponent(routeConfig)))
  } else {
    routeProps.component = route.component
  }

  return React.createElement(Route, routeProps)
}

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
export function routedApp(routesConfig, basePath = '') {
  return () => {
    const RoutedApp = React.createElement(Router, {
      history: hashHistory
    }, React.createElement(Route, {
      path: basePath
    }, React.createElement(Switch, {}, routesConfig.map(route => getRouteComponent(route)))))

    return RoutedApp
  }
}
