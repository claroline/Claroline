import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const AlertBlock = props =>
  <div className={classes('alert alert-detailed', 'alert-'+props.type)}>
    <span className={classes('alert-icon fa fa-fw', {
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
