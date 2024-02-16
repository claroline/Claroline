import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const WarningIcon = props =>
  <span
    className={classes('fa answer-warning-span', props.className, {
      'fa-check': props.valid,
      'fa-times': !props.valid
    })}
    aria-hidden="true"
  />

WarningIcon.propTypes = {
  className: T.string,
  valid: T.bool.isRequired
}

export {
  WarningIcon
}
