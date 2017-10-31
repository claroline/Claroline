import React from 'react'
import {PropTypes as T} from 'prop-types'

const Radios = props =>
  <fieldset>
    {props.options.map(option =>
      <div
        className={props.inline ? 'radio-inline' : 'radio'}
        key={option.value}
      >
        <label>
          <input
            type="radio"
            name={props.groupName}
            value={option.value}
            checked={option.value === props.checkedValue}
            disabled={props.disabled}
            onChange={() => props.onChange(option.value)}
          />

          {option.label}
        </label>
      </div>
    )}
  </fieldset>

Radios.propTypes = {
  groupName: T.string.isRequired,
  options: T.arrayOf(T.shape({
    value: T.oneOfType([T.string, T.number]).isRequired,
    label: T.string.isRequired
  })).isRequired,
  checkedValue: T.oneOfType([T.string, T.number]).isRequired,
  inline: T.bool,
  disabled: T.bool,
  onChange: T.func.isRequired
}

export {
  Radios
}
