import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const Dot = (props) =>
  <div className={classes(props.className, 'p-1 rounded-circle', `bg-${props.variant}-subtle`)} aria-hidden={true}>
    <div className={classes('p-1 rounded-circle', `bg-${props.variant}`)} />
  </div>

Dot.propTypes = {
  className: T.string,
  variant: T.oneOf([
    'primary',
    'secondary',
    'success',
    'warning',
    'danger',
    'info',
  ]).isRequired
}

export {
  Dot
}
