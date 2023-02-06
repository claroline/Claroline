import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

const AlertBlock = props =>
  <div
    {...omit(props, 'className', 'icon', 'type', 'title', 'children')}
    className={classes('alert alert-detailed', 'alert-'+props.type, props.className)}
    role="alert"
  >
    <span className="alert-icon">
      <span className={classes(!props.icon && 'fa fa-fw', props.icon || {
        'fa-lightbulb':        'info' === props.type,
        'fa-check':       'success' === props.type,
        'fa-exclamation': 'warning' === props.type,
        'fa-xmark':       'danger' === props.type
      })} />
    </span>

    <div className="alert-message">
      {props.title &&
        <b className="alert-title">{props.title}</b>
      }

      {props.children}
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
