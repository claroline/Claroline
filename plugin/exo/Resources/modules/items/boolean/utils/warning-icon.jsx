import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

export const WarningIcon = props => {
  return props.valid ?
     <span className={classes(props.className, 'fa fa-check answer-warning-span')} aria-hidden="true"></span> :
     <span className={classes(props.className, 'fa fa-times answer-warning-span')} aria-hidden="true"></span>
}

WarningIcon.propTypes = {
  valid: T.bool.isRequired,
  className: T.string
}
