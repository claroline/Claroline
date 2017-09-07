import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

/**
 * Button element.
 *
 * @param props
 * @constructor
 */
const Button = props =>
  <button
    type="button"
    role="button"
    disabled={props.disabled}
    className={classes(
      'btn',
      props.className,
      {disabled: props.disabled}
    )}
    onClick={(e) => !props.disabled && props.onClick(e)}
  >
    {props.children}
  </button>

Button.propTypes = {
  children: T.node.isRequired,
  disabled: T.bool,
  onClick: T.func.isRequired,
  className: T.string
}

Button.defaultProps = {
  disabled: false
}

export {
  Button
}
