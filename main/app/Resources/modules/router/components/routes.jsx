import React from 'react'
import {PropTypes as T} from 'prop-types'
import {
  Route as BaseRoute,
  Redirect,
  Switch
} from 'react-router-dom'

import {toKey} from '#/main/core/scaffolding/text'
import {Route} from '#/main/app/router/components/route'
import {Route as RouteTypes} from '#/main/app/router/prop-types'

const Routes = props =>
  <BaseRoute
    key={props.path}
    path={props.path}
    exact={props.exact}
  >
    <Switch>
      {props.routes
        .filter(route => !route.disabled)
        .map((route) => route.routes ?
          <Routes
            {...route}
            key={`route-${toKey(route.path)}`}
            path={props.path+route.path}
          /> :
          <Route
            {...route}
            key={`route-${toKey(route.path)}`}
            path={props.path+route.path}
          />
        )
      }

      {props.redirect
        .filter(redirect => !redirect.disabled)
        .map((redirect, redirectIndex) =>
          <Redirect
            {...redirect}
            key={`redirect-${redirectIndex}`}
            from={props.path+redirect.from}
            to={props.path+redirect.to}
          />
        )
      }
    </Switch>
  </BaseRoute>

Routes.propTypes = {
  path: T.string,
  exact: T.bool,
  routes: T.arrayOf(
    T.shape(RouteTypes.propTypes).isRequired
  ),
  redirect: T.arrayOf(T.shape({
    disabled: T.bool,
    from: T.string.isRequired,
    to: T.string.isRequired,
    exact: T.bool
  }))
}

Routes.defaultProps = {
  path: '',
  exact: false,
  redirect: []
}

export {
  Routes
}
