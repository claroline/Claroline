import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {toKey} from '#/main/core/scaffolding/text'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {Checkbox} from '#/main/app/input/components/checkbox'

const parseValue = (value) => !isNaN(value) ? parseFloat(value) : value

/**
 * Renders a list of radios checkbox inputs.
 */
const Checkboxes = props => {
  const choiceValues = Object.keys(props.choices)
  if (0 === choiceValues.length) {
    return (
      <span className="text-secondary d-block">{trans('no_choice')}</span>
    )
  }

  return (
    <div id={props.id} className={props.className}>
      {choiceValues.map(choiceValue =>
        <Checkbox
          key={choiceValue}
          id={`${props.id}-${toKey(choiceValue)}`}
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
    </div>
  )
}

implementPropTypes(Checkboxes, DataInputTypes, {
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
