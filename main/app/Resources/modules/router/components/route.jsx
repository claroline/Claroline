import React, {Component} from 'react'
import {Route as BaseRoute} from 'react-router-dom'
import get from 'lodash/get'

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

  componentDidUpdate(prevProps) {
    if (this.props.location && get(this.props, 'location.pathname') !== get(prevProps, 'location.pathname')) {
      if (this.props.onLeave) {
        this.props.onLeave(prevProps.computedMatch.params)
      }

      if (this.props.onEnter) {
        this.props.onEnter(this.props.computedMatch.params)
      }
    }
  }

  componentWillUnmount() {
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

export {
  Route
}
