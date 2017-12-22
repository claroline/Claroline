import React from 'react'
import {PropTypes as T} from 'prop-types'

const getCheckedValues = (e) => {
  const values = []
  document.querySelectorAll(`input[type=checkbox][name="${e.target.name}"]:checked`).forEach(c => values.push(c.value))

  return values
}

const Checkboxes = props =>
  <fieldset onChange={e => props.onChange(getCheckedValues(e))}>
    {props.options.map(option =>
      <div
        className={props.inline ? 'checkbox-inline' : 'checkbox'}
        key={option.value}
      >
        <label>
          <input
            type="checkbox"
            name={`${props.groupName}[]`}
            value={option.value}
            checked={option.value === props.checkedValues.find(cv => cv === option.value)}
            disabled={props.disabled}
            onChange={() => {}}
          />

          {option.label}
        </label>
      </div>
    )}
  </fieldset>

Checkboxes.propTypes = {
  groupName: T.string.isRequired,
  options: T.arrayOf(T.shape({
    value: T.string.isRequired,
    label: T.string.isRequired
  })).isRequired,
  checkedValues: T.array.isRequired,
  inline: T.bool,
  disabled: T.bool,
  onChange: T.func.isRequired
}

export {
  Checkboxes
}