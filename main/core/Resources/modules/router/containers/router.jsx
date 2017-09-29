import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import {
  HashRouter as Router,
  Route as BaseRoute,
  Switch,
  withRouter
} from 'react-router-dom'

import {Route as RouteTypes} from '#/main/core/router/prop-types'

const Route = props =>
  <BaseRoute
    path={props.path}
    exact={props.exact}
    component={props.component}
  />

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
          onEnter={routeConfig.onEnterAction ? () => props.dispatchRouteAction(routeConfig.onEnterAction()) : undefined}
          onLeave={routeConfig.onLeaveAction ? () => props.dispatchRouteAction(routeConfig.onLeaveAction()) : undefined}
        />
      )}
    </Switch>
  </BaseRoute>

Routes.propTypes = {
  path: T.string.isRequired,
  routes: T.arrayOf(
    T.shape(RouteTypes.propTypes).isRequired // todo : allow more than one nesting in prop-types
  ),
  dispatchRouteAction: T.func.isRequired
}

Routes.defaultProps = RouteTypes.defaultProps

class CustomRouter extends Component {
  constructor(props) {
    super(props)

    // register to history changes to dispatch correct actions
    //this.props.history.listen(this.props.routeChange)
  }

  render() {
    return (
      <Routes
        path={this.props.basePath}
        routes={this.props.routes}
        dispatchRouteAction={this.props.dispatchRouteAction}
      />
    )
  }
}

CustomRouter.propTypes = {
  history: T.shape({
    listen: T.func.isRequired
  }).isRequired,
  basePath: T.string,
  routes: T.array.isRequired,
  dispatchRouteAction: T.func.isRequired
}

CustomRouter.defaultProps = {
  basePath: ''
}

function mapDispatchToProps(dispatch) {
  return {
    dispatchRouteAction(action) {
      dispatch(action)
    }
  }
}

const ConnectedRouter = withRouter(connect(null, mapDispatchToProps)(CustomRouter))

const RouterContainer = props =>
  <Router>
    <ConnectedRouter {...props} />
  </Router>

export {
  RouterContainer
}