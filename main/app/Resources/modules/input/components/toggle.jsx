import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const Toggle = props =>
  <button
    type="button"
    className={classes('toggle-input', props.className, {
      disabled: props.disabled,
      on: props.active,
      off: !props.active
    })}
    disabled={props.disabled}
    onClick={(e) => {
      props.onChange(!props.value)

      e.preventDefault()
      e.stopPropagation()
    }}
  >
    {props.active ? 'on' : 'off'}
  </button>

Toggle.propTypes = {
  className: T.string,
  active: T.bool,
  disabled: T.bool,
  onChange: T.func.isRequired
}

Toggle.defaultProps = {
  active: false
}

export {
  Toggle
}
