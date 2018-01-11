import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const Checkbox = props =>
  <div className={classes('checkbox', props.className)}>
    <label htmlFor={props.id}>
      <input
        id={props.id}
        type="checkbox"
        checked={props.checked}
        disabled={props.disabled}
        onChange={e => props.onChange(e.target.checked)}
      />

      {props.checked && props.labelChecked ? props.labelChecked : props.label}
    </label>
  </div>

Checkbox.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  label: T.string.isRequired,
  labelChecked: T.string,
  checked: T.bool.isRequired,
  disabled: T.bool,
  onChange: T.func.isRequired
}

Checkbox.defaultProps = {
  disabled: false
}

export {
  Checkbox
}
