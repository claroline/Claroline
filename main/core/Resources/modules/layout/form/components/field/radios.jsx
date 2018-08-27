import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

const parseValue = (value) => !isNaN(value) ? parseFloat(value) : value

/**
 * Renders a list of radio inputs.
 */
const Radios = props =>
  <fieldset>
    {Object.keys(props.choices).map(choiceValue =>
      <div
        key={choiceValue}
        className={classes({
          'radio-inline': props.inline,
          'radio': !props.inline
        })}
      >
        <label>
          <input
            type="radio"
            name={props.id}
            value={choiceValue}
            checked={parseValue(choiceValue) === props.value}
            disabled={props.disabled}
            onChange={() => props.onChange(parseValue(choiceValue))}
          />

          {props.choices[choiceValue]}
        </label>
      </div>
    )}
  </fieldset>

implementPropTypes(Radios, FormFieldTypes, {
  value: T.oneOfType([T.string, T.number]),

  // custom props
  choices: T.object.isRequired,
  inline: T.bool
}, {
  value: '',
  inline: true
})

export {
  Radios
}
