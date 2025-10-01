import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const Dot = (props) =>
  <div className={classes(props.className, 'd-inline-block p-1 rounded-circle', `bg-${props.variant}-subtle`)} aria-hidden={true}>
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
    'info'
  ]).isRequired
}

const DotColor = (props) => {
  return (
    <div className={classes(props.className, 'd-inline-block p-1 rounded-circle')} aria-hidden={true} style={{background: `rgba(from ${props.color} r g b / .25)`}}>
      <div className="p-1 rounded-circle" style={{background: props.color}} />
    </div>
  )
}

DotColor.propTypes = {
  className: T.string,
  color: T.string.isRequired
}

export {
  Dot,
  DotColor
}
