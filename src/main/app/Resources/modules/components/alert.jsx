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
    <span className={classes('alert-icon fa fa-fw', props.icon || {
      'fa-info-circle': 'info' === props.type,
      'fa-circle-check': 'success' === props.type,
      'fa-warning': 'warning' === props.type,
      'fa-circle-xmark': 'danger' === props.type
    })} aria-hidden={true} />

    <span className="alert-body" role="presentation">
      {props.title &&
        <h4 className={classes('alert-heading', `text-${props.type}-emphasis`)}>{props.title}</h4>
      }

      {props.children}
    </span>
  </div>

Alert.propTypes = {
  className: T.string,
  type: T.oneOf(['info', 'success', 'warning', 'danger']),
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
