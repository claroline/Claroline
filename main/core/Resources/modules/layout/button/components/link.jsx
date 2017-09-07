import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

/**
 * Link element.
 *
 * @param props
 * @constructor
 */
const Link = props =>
  <a
    href={!props.disabled ? props.target : ''}
    disabled={props.disabled}
    className={classes(
      'btn',
      props.className,
      {disabled: props.disabled}
    )}
  >
    {props.children}
  </a>

Link.propTypes = {
  children: T.node.isRequired,
  disabled: T.bool,
  target: T.string,
  className: T.string
}

Link.defaultProps = {
  disabled: false
}

export {
  Link
}
