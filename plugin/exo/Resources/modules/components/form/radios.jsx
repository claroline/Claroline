import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

export const Radios = props =>
  <fieldset>
    {props.options.map(option =>
      <div
        className={classes(
         props.inline ? 'radio-inline' : 'radio'
        )}
        key={option.value}
      >
        <label>
          <input
            type="radio"
            name={props.groupName}
            value={option.value}
            checked={option.value === props.checkedValue}
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
    value: T.string.isRequired,
    label: T.string.isRequired
  })).isRequired,
  checkedValue: T.string.isRequired,
  inline: T.bool,
  onChange: T.func.isRequired
}
