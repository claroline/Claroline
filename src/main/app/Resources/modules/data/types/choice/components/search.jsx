import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/app/data/types/prop-types'
import {ChoiceInput} from '#/main/app/data/types/choice/components/input'

const ChoiceSearch = (props) => {
  let value
  if (props.search) {
    value = Array.isArray(props.search) ? props.search : [props.search]
  }

  return (
    <ChoiceInput
      id={props.id}
      className="data-filter"
      choices={props.choices}
      value={value}
      onChange={props.updateSearch}
      size={props.size}

      inline={false}
      multiple={true}
      condensed={props.condensed}
      disabled={props.disabled}
    />
  )
}

implementPropTypes(ChoiceSearch, DataSearchTypes, {
  choices: T.object.isRequired,
  disabledChoices: T.arrayOf(T.string),
  multiple: T.bool, // Attention: Finder must be able to handle it
  inline: T.bool,
  condensed: T.bool
}, {
  choices: {},
  inline: true,
  multiple: false,
  condensed: false
})

export {
  ChoiceSearch
}
