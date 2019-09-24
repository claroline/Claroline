import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {toKey} from '#/main/core/scaffolding/text'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {Checkbox} from '#/main/core/layout/form/components/field/checkbox'

const parseValue = (value) => !isNaN(value) ? parseFloat(value) : value

/**
 * Renders a list of radios checkbox inputs.
 */
const Checkboxes = props => {
  const choiceValues = Object.keys(props.choices)
  if (0 === choiceValues.length) {
    return (
      <em className="text-muted">{trans('no_choice')}</em>
    )
  }

  return (
    <fieldset>
      {choiceValues.map(choiceValue =>
        <Checkbox
          key={choiceValue}
          id={toKey(choiceValue)}
          label={props.choices[choiceValue]}
          checked={props.value ? -1 !== props.value.indexOf(parseValue(choiceValue)) : false}
          disabled={props.disabled || -1 !== props.disabledChoices.indexOf(choiceValue)}
          inline={props.inline}
          onChange={(checked) => {
            let value = [].concat(props.value || [])

            if (checked) {
              value.push(parseValue(choiceValue))
            } else {
              value.splice(value.indexOf(parseValue(choiceValue)), 1)
            }

            props.onChange(value)
          }}
        />
      )}
    </fieldset>
  )
}

implementPropTypes(Checkboxes, FormFieldTypes, {
  value: T.array,
  choices: T.object.isRequired,
  disabledChoices: T.arrayOf(T.string),
  inline: T.bool
}, {
  value: [],
  disabledChoices: [],
  inline: true
})

export {
  Checkboxes
}
