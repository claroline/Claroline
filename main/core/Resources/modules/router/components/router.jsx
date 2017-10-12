import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {
  Router,
  Route as BaseRoute,
  Switch
} from 'react-router-dom'

import {history} from '#/main/core/router/history'
import {Route as RouteTypes} from '#/main/core/router/prop-types'

// todo : implement canEnter for security purpose

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
  >
    <Switch>
      {props.routes.map(routeConfig => routeConfig.routes ?
        <Routes
          {...routeConfig}
          key={props.path}
          dispatchRouteAction={props.dispatchRouteAction}
        /> :
        <Route
          {...routeConfig}
          key={props.path}
          onEnter={routeConfig.onEnter}
          onLeave={routeConfig.onLeave}
        />
      )}
    </Switch>
  </BaseRoute>

Routes.propTypes = {
  path: T.string.isRequired,
  routes: T.arrayOf(
    T.shape(RouteTypes.propTypes).isRequired // todo : allow more than one nesting in prop-types
  )
}

Routes.defaultProps = RouteTypes.defaultProps

const CustomRouter = props =>
  <Router history={history}>
    <Routes
      path={props.basePath}
      routes={props.routes}
    />
  </Router>

CustomRouter.propTypes = {
  basePath: T.string,
  routes: T.array.isRequired
}

CustomRouter.defaultProps = {
  basePath: ''
}

export {
  CustomRouter as Router
}