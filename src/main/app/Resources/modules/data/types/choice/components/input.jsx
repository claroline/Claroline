import React from 'react'
import classes from 'classnames'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {Select} from '#/main/app/input/components/select'
import {Checkboxes} from '#/main/app/input/components/checkboxes'
import {Radios} from '#/main/app/input/components/radios'

const ChoiceInput = props => {
  const className = classes('choice-control', props.className, props.size)

  if (!props.condensed) {
    if (props.multiple) {
      return (
        <Checkboxes {...props} className={className} />
      )
    }

    if (!props.multiple) {
      return (
        <Radios {...props} className={className} />
      )
    }
  }

  return (
    <Select {...props} className={className} />
  )
}

implementPropTypes(ChoiceInput, DataInputTypes, {
  // more precise value type
  value: T.oneOfType([T.string, T.number, T.array]),
  // custom props
  choices: T.object.isRequired,
  disabledChoices: T.arrayOf(T.string),
  multiple: T.bool,
  inline: T.bool,
  condensed: T.bool
}, {
  choices: {},
  inline: false,
  multiple: false,
  condensed: false
})

export {
  ChoiceInput
}
