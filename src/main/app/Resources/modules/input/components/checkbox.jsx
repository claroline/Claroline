import React, {useId} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const Checkbox = props => {
  const checkId = useId()

  return (
    <div className={classes('form-check', {
      'form-check-inline': props.inline,
      'form-switch': props.switch
    }, props.className)} role="presentation">
      <input
        id={checkId}
        className="form-check-input"
        type="checkbox"
        checked={props.checked}
        disabled={props.disabled}
        aria-checked={props.checked}
        onChange={e => props.onChange(e.target.checked)}
      />

      <label htmlFor={checkId} className="form-check-label d-block">
        {props.label}
        {props.description &&
          <p className="text-body-secondary fs-sm">{props.description}</p>
        }
      </label>
    </div>
  )
}

Checkbox.propTypes = {
  className: T.string,
  label: T.node.isRequired,
  description: T.string,
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
