import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

const AlertBlock = props =>
  <div
    {...omit(props, 'className', 'icon', 'type', 'title', 'children')}
    className={classes('alert alert-detailed', 'alert-'+props.type, props.className)}
  >
    <span className={classes('alert-icon', !props.icon && 'fa fa-fw', props.icon || {
      'fa-info-circle':          'info' === props.type,
      'fa-check-circle':         'success' === props.type,
      'fa-exclamation-triangle': 'warning' === props.type,
      'fa-times-circle':         'danger' === props.type
    })} />

    <div className="alert-content">
      {props.title &&
        <b className="alert-title">{props.title}</b>
      }

      <div className="alert-text">{props.children}</div>
    </div>
  </div>

AlertBlock.propTypes = {
  className: T.string,
  icon: T.string,
  type: T.oneOf(['info', 'success', 'warning', 'danger']),
  title: T.string,
  children: T.node.isRequired
}

AlertBlock.defaultProps = {
  type: 'info'
}

export {
  AlertBlock
}
