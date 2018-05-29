import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {
  Redirect,
  HashRouter,
  MemoryRouter,
  Route as BaseRoute,
  Switch
} from 'react-router-dom'

import {Route as RouteTypes} from '#/main/app/router/prop-types'

/**
 * Creates a custom Route component to bind redux action on enter and leave.
 *
 * NB. This is not really aesthetic because component should react to
 * redux and not call it in it's mounting lifecycle.
 */
class Route extends Component {
  constructor(props) {
    super(props)

    if (props.onEnter) {
      props.onEnter(props.computedMatch.params)
    }
  }

  componentWillReceiveProps(nextProps) {
    // todo find a way to block mounting (for async)
    if (this.props.location.pathname !== nextProps.location.pathname) {
      if (this.props.onLeave) {
        this.props.onLeave(this.props.computedMatch.params)
      }

      if (nextProps.onEnter) {
        nextProps.onEnter(nextProps.computedMatch.params)
      }
    }
  }

  componentWillUnmount() {
    // todo find a way to block unmounting(for async)
    if (this.props.onLeave) {
      this.props.onLeave(this.props.computedMatch)
    }
  }

  render() {
    return (
      <BaseRoute
        path={this.props.path}
        exact={this.props.exact}
        component={this.props.component}
        render={this.props.render}
      />
    )
  }
}

Route.propTypes = RouteTypes.propTypes
Route.defaultProps = RouteTypes.defaultProps

const Routes = props =>
  <BaseRoute
    key={props.path}
    path={props.path}
    exact={props.exact}
  >
    <Switch>
      {props.routes
        .filter(route => !route.disabled)
        .map((route, routeIndex) => route.routes ?
          <Routes
            {...route}
            key={`route-${routeIndex}`}
          /> :
          <Route
            {...route}
            key={`route-${routeIndex}`}
          />
        )
      }

      {props.redirect
        .filter(redirect => !redirect.disabled)
        .map((redirect, redirectIndex) =>
          <Redirect
            {...redirect}
            key={`redirect-${redirectIndex}`}
          />
        )
      }
    </Switch>
  </BaseRoute>

Routes.propTypes = {
  path: T.string,
  exact: T.bool,
  routes: T.arrayOf(
    T.shape(RouteTypes.propTypes).isRequired // todo : allow more than one nesting in prop-types
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

const Router = props => !props.embedded ?
  <HashRouter>
    {props.children}
  </HashRouter> :
  <MemoryRouter>
    {props.children}
  </MemoryRouter>

Router.propTypes = {
  children: T.node,
  embedded: T.bool
}

Router.defaultProps = {
  embedded: false
}

export {
  Router,
  Routes,
  Route
}
