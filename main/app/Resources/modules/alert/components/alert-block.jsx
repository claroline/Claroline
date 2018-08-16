import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {HtmlText} from '#/main/core/layout/components/html-text'

const AlertBlock = props =>
  <div className={classes('alert alert-detailed', 'alert-'+props.type)}>
    <span className={classes('alert-icon fa fa-fw', {
      'fa-info-circle':          'info' === props.type,
      'fa-check-circle':         'success' === props.type,
      'fa-exclamation-triangle': 'warning' === props.type,
      'fa-times-circle':         'danger' === props.type
    })} />
    <div className="alert-content">
      <b className="alert-title">{props.title}</b>
      <HtmlText className="alert-text">{props.message}</HtmlText>
    </div>
  </div>

AlertBlock.propTypes = {
  type: T.oneOf(['info', 'success', 'warning', 'danger']),
  title: T.string.isRequired,
  message: T.string.isRequired
}

AlertBlock.defaultProps = {
  type: 'info'
}

export {
  AlertBlock
}
