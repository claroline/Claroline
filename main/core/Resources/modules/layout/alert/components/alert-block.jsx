import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const AlertBlock = props =>
  <div className={classes('alert alert-detailed', 'alert-'+props.type)}>
    <span className={classes('fa fa-fw', {
      'fa-info-circle': 'info' === props.type,
      'fa-check-circle': 'success' === props.type,
      'fa-exclamation-triangle': 'warning' === props.type,
      'fa-times-circle': 'danger' === props.type
    })} />
    <div className="alert-content">
      <b>{props.title}</b>
      {props.description}
    </div>
  </div>

AlertBlock.propTypes = {
  type: T.oneOf(['info', 'success', 'warning', 'danger']),
  title: T.string.isRequired,
  description: T.string
}

AlertBlock.defaultProps = {
  type: 'info'
}

export {
  AlertBlock
}
