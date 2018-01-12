import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

const getCheckedValues = (e) => {
  const values = []
  document.querySelectorAll(`input[type=checkbox][name="${e.target.name}"]:checked`).forEach(c => values.push(c.value))

  return values
}

const Checkboxes = props =>
  <fieldset
    onChange={e => props.onChange(getCheckedValues(e))}
  >
    {props.options.map(option =>
      <div
        className={props.inline ? 'checkbox-inline' : 'checkbox'}
        key={option.value}
      >
        <label>
          <input
            type="checkbox"
            name={`${props.id}[]`}
            value={option.value}
            checked={option.value === props.value.find(cv => cv === option.value)}
            disabled={props.disabled}
            onChange={() => {}}
          />

          {option.label}
        </label>
      </div>
    )}
  </fieldset>

implementPropTypes(Checkboxes, FormFieldTypes, {
  value: T.array,
  options: T.arrayOf(T.shape({ // todo use same format than enum
    value: T.string.isRequired,
    label: T.string.isRequired
  })).isRequired,
  inline: T.bool
}, {
  value: [],
  inline: true
})

export {
  Checkboxes
}