import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const Badge = (props) =>
  <span className={classes('badge', props.className, {
    [`text-bg-${props.variant}`]: !props.subtle,
    [`bg-${props.variant}-subtle text-${props.variant}-emphasis`]: props.subtle
  })} style={props.style}>
    {props.children}
  </span>

Badge.propTypes = {
  className: T.string,
  subtle: T.bool.isRequired,
  variant: T.oneOf(['primary', 'secondary', 'success', 'warning', 'danger', 'info']).isRequired,
  style: T.object,
  children: T.any
}

Badge.defaultProps = {
  subtle: false,
  variant: 'secondary'
}

export {
  Badge
}
