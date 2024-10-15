import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const Checkbox = props =>
  <div className={classes('form-check', {
    'form-check-inline': props.inline,
    'form-switch': props.switch
  }, props.className)} role="presentation">
    <input
      id={props.id}
      className="form-check-input"
      type="checkbox"
      checked={props.checked}
      disabled={props.disabled}
      onChange={e => props.onChange(e.target.checked)}
    />

    <label htmlFor={props.id} className="form-check-label d-block">
      {props.checked && props.labelChecked ? props.labelChecked : props.label}
    </label>
  </div>

Checkbox.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  label: T.node.isRequired,
  labelChecked: T.node,
  checked: T.bool.isRequired,
  disabled: T.bool,
  inline: T.bool,
  switch: T.bool,
  onChange: T.func.isRequired
}

Checkbox.defaultProps = {
  disabled: false,
  inline: false,
  switch: false
}

export {
  Checkbox
}
