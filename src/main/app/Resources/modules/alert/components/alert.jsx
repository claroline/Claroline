import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

/**
 * Renders a basic alert message with status contextualization (icon + colors).
 */
const Alert = props =>
  <div
    {...omit(props, 'type', 'children')}
    className={classes('alert', 'alert-'+props.type)}
  >
    <span className={classes('fa fa-fw icon-with-text-right', {
      'fa-info-circle': 'info' === props.type,
      'fa-check-circle': 'success' === props.type,
      'fa-exclamation-triangle': 'warning' === props.type,
      'fa-times-circle': 'danger' === props.type
    })} aria-hidden={true} />

    {props.children}
  </div>

Alert.propTypes = {
  type: T.oneOf(['info', 'success', 'warning', 'danger']),
  children: T.node.isRequired
}

Alert.defaultProps = {
  type: 'info'
}

export {
  Alert
}
