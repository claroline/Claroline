import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const parseValue = (value) => !isNaN(value) ? parseFloat(value) : value

const Radio = props =>
  <div
    className={classes('form-check', props.className, {
      'form-check-inline': props.inline
    })}
    role="presentation"
  >
    <input
      id={props.id}
      className="form-check-input"
      type="radio"
      value={props.value}
      checked={props.checked}
      disabled={props.disabled}
      onChange={() => props.onChange(parseValue(props.value))}
    />

    <label htmlFor={props.id} className="form-check-label d-block">
      {props.label}
    </label>
  </div>

Radio.propTypes = {
  id: T.oneOfType([T.string, T.number]).isRequired,
  className: T.string,
  label: T.node.isRequired,
  value: T.oneOfType([T.string, T.number]),
  inline: T.bool,
  checked: T.bool,
  disabled: T.bool,
  onChange: T.func.isRequired
}

Radio.defaultProps = {
  checked: false,
  disabled: false
}

export {
  Radio
}
