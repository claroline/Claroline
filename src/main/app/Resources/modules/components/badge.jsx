import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const Badge = (props) =>
  <span className={classes('badge', props.className, {
    [`bg-${props.variant}`]: !props.subtle,
    [`bg-${props.variant}-subtle text-${props.variant}-emphasis`]: props.subtle
  })}>
    {props.children}
  </span>

Badge.propTypes = {
  subtle: T.bool.isRequired,
  variant: T.oneOf(['primary', 'secondary', 'success', 'warning', 'danger', 'info']).isRequired
}

Badge.defaultProps = {
  subtle: false,
  variant: 'secondary'
}

export {
  Badge
}
