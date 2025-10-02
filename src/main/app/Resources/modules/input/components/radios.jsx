import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {Radio} from '#/main/app/input/components/radio'

const parseValue = (value) => !isNaN(value) ? parseFloat(value) : value

/**
 * Renders a list of radio inputs.
 */
const Radios = props => {
  const choiceValues = Object.keys(props.choices)

  if (0 === choiceValues.length) {
    return (
      <em className="text-body-tertiary d-block">{trans('no_choice')}</em>
    )
  }

  return (
    <>
      {!props.noEmpty &&
        <Radio
          key="empty-value"
          name={props.id}
          label={props.placeholder || trans('none')}
          value=""
          inline={props.inline}
          checked={null === props.value}
          onChange={() => props.onChange(null)}
        />
      }

      {choiceValues.map((choiceValue) =>
        <Radio
          key={choiceValue}
          {...(typeof props.choices[choiceValue] === 'string' ? {label: props.choices[choiceValue]} : props.choices[choiceValue])}
          name={props.id}
          value={choiceValue}
          inline={props.inline}
          checked={parseValue(choiceValue) === props.value}
          disabled={props.disabled || -1 !== props.disabledChoices.indexOf(choiceValue)}
          onChange={props.onChange}
        />
      )}
    </>
  )
}

implementPropTypes(Radios, DataInputTypes, {
  value: T.oneOfType([T.string, T.number]),

  // custom props
  choices: T.object.isRequired,
  disabledChoices: T.arrayOf(T.string),
  inline: T.bool,
  noEmpty: T.bool
}, {
  value: '',
  disabledChoices: [],
  inline: true,
  noEmpty: true
})

export {
  Radios
}
