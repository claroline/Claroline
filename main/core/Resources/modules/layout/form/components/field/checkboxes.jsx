import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

const parseValue = (value) => !isNaN(value) ? parseFloat(value) : value

/**
 * Renders a list of radios checkbox inputs.
 */
const Checkboxes = props =>
  <fieldset>
    {Object.keys(props.choices).map(choiceValue =>
      <div
        key={choiceValue}
        className={classes({
          'checkbox-inline': props.inline,
          'checkbox': !props.inline
        })}
      >
        <label>
          <input
            type="checkbox"
            name={`${props.id}[]`}
            value={choiceValue}
            checked={props.value ? -1 !== props.value.indexOf(parseValue(choiceValue)) : false}
            disabled={props.disabled}
            onChange={(e) => {
              let value = [].concat(props.value || [])

              if (e.target.checked) {
                value.push(parseValue(choiceValue))
              } else {
                value.splice(value.indexOf(parseValue(choiceValue)), 1)
              }

              props.onChange(value)
            }}
          />

          {props.choices[choiceValue]}
        </label>
      </div>
    )}
  </fieldset>

implementPropTypes(Checkboxes, FormFieldTypes, {
  value: T.array,
  choices: T.object.isRequired,
  inline: T.bool
}, {
  value: [],
  inline: true
})

export {
  Checkboxes
}
