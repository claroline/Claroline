import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

const getCheckedValues = (e) => {
  const values = []
  document.querySelectorAll(`input[type=checkbox][name="${e.target.name}"]:checked`).forEach(c => values.push(c.value))

  return values
}

/**
 * Renders a list of radios checkbox inputs.
 */
const Checkboxes = props =>
  <fieldset
    onChange={e => props.onChange(getCheckedValues(e))}
  >
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
            checked={-1 !== props.value.indexOf(choiceValue)}
            disabled={props.disabled}
            onChange={() => {}}
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
