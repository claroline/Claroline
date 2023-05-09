import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

/**
 * Renders a basic alert message with status contextualization (icon + color).
 */
const Alert = props =>
  <div
    {...omit(props, 'type', 'children')}
    className={classes('alert', 'alert-'+props.type, props.className)}
    role="alert"
  >
    <span className="alert-icon" role="presentation">
      <span className={classes('fa fa-fw', props.icon || {
        'fa-lightbulb': 'info' === props.type,
        'fa-check': 'success' === props.type,
        'fa-exclamation': 'warning' === props.type,
        'fa-xmark': 'danger' === props.type
      })} aria-hidden={true} />
    </span>

    <span className="alert-message" role="presentation">
      {props.title &&
        <b className="alert-title">{props.title}</b>
      }

      {props.children}
    </span>
  </div>

Alert.propTypes = {
  className: T.string,
  type: T.oneOf(['primary', 'secondary', 'info', 'success', 'warning', 'danger']),
  icon: T.string,
  title: T.string,
  children: T.node.isRequired
}

Alert.defaultProps = {
  type: 'info'
}

export {
  Alert
}
