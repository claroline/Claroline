import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

/**
 * Renders a list of radio inputs.
 */
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
            name={props.id}
            value={option.value}
            checked={option.value === props.value}
            disabled={props.disabled}
            onChange={() => props.onChange(option.value)}
          />

          {option.label}
        </label>
      </div>
    )}
  </fieldset>

implementPropTypes(Radios, FormFieldTypes, {
  value: T.oneOfType([T.string, T.number]),
  options: T.arrayOf(T.shape({ // todo use same format than enum
    value: T.string.isRequired,
    label: T.string.isRequired
  })).isRequired,
  inline: T.bool
}, {
  value: '',
  inline: true
})

export {
  Radios
}
