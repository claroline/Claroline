import React from 'react'
import classes from 'classnames'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {Select} from '#/main/app/input/components/select'
import {Checkboxes} from '#/main/app/input/components/checkboxes'
import {Radios} from '#/main/app/input/components/radios'

const ChoiceInput = props => {
  const className = classes('choice-control', props.className, props.size)

  /**
   * Removes unknown choices to avoid obscure validation error.
   * (in list configuration, you often get these kind of errors because of deleted / renamed column in the list definition).
   *
   * This will also permit to clean the DB over time.
   */
  const sanitize = (value) => {
    let sanitized = value
    if (props.multiple) {
      sanitized = (value || []).filter((choice) => !!props.choices[choice])
    }

    props.onChange(sanitized)
  }

  if (!props.condensed) {
    if (props.multiple) {
      return (
        <Checkboxes {...props} className={className} onChange={sanitize} />
      )
    }

    if (!props.multiple) {
      return (
        <Radios {...props} className={className} onChange={sanitize} />
      )
    }
  }

  return (
    <Select {...props} className={className} onChange={sanitize} />
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
