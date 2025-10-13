import React, {useId} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const parseValue = (value) => !isNaN(value) ? parseFloat(value) : value

const Radio = props => {
  const radioId = useId()

  return (
    <div
      className={classes('form-check', props.className, {
        'form-check-inline': props.inline
      })}
      role="presentation"
    >
      <input
        id={radioId}
        className="form-check-input"
        type="radio"
        value={props.value}
        checked={props.checked}
        disabled={props.disabled}
        aria-checked={props.checked}
        onChange={() => props.onChange(parseValue(props.value))}
        name={props.name}
      />

      <label htmlFor={radioId} className="form-check-label d-block">
        {props.label}
        {props.description &&
          <p className="text-body-secondary fs-sm mb-0">{props.description}</p>
        }
      </label>
    </div>
  )
}

Radio.propTypes = {
  className: T.string,
  name: T.string.isRequired,
  label: T.node.isRequired,
  description: T.string,
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
