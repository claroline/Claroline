import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {DataGroup as DataGroupTypes, DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {SelectGroup} from '#/main/core/layout/form/components/group/select-group'
import {CheckboxesGroup} from '#/main/core/layout/form/components/group/checkboxes-group'
import {RadiosGroup} from '#/main/core/layout/form/components/group/radios-group'

// todo : adds min and max values for multiple: true

/**
 * @deprecated
 */
const ChoiceGroup = props => {
  if (!props.condensed && props.multiple) {
    return (<CheckboxesGroup {...props} />)
  }

  if (!props.condensed && !props.multiple) {
    return (<RadiosGroup {...props} />)
  }

  return (<SelectGroup {...props} />)
}

implementPropTypes(ChoiceGroup, [DataGroupTypes, DataInputTypes], {
  // more precise value type
  value: T.oneOfType([T.string, T.number, T.array]),
  // custom props
  choices: T.object.isRequired,
  disabledChoices: T.arrayOf(T.string),
  multiple: T.bool,
  condensed: T.bool
}, {
  multiple: false,
  condensed: false
})

export {
  ChoiceGroup
}
