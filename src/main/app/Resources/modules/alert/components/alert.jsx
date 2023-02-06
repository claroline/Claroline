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
    role="alert"
  >
    <span className="alert-icon" role="presentation">
      <span className={classes('fa fa-fw', {
        'fa-lightbulb': 'info' === props.type,
        'fa-check': 'success' === props.type,
        'fa-exclamation': 'warning' === props.type,
        'fa-xmark': 'danger' === props.type
      })} aria-hidden={true} />
    </span>

    <span className="alert-message" role="presentation">
      {props.children}
    </span>
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
