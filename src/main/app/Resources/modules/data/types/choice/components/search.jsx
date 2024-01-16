import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/app/data/types/prop-types'
import {ChoiceInput} from '#/main/app/data/types/choice/components/input'

const ChoiceSearch = (props) =>
  <ChoiceInput
    id={props.id}
    className="data-filter"
    choices={props.choices}
    value={props.search}
    onChange={props.updateSearch}
    size={props.size}

    inline={props.inline}
    multiple={false}
    condensed={props.condensed}
  />

implementPropTypes(ChoiceSearch, DataSearchTypes, {
  choices: T.object.isRequired,
  disabledChoices: T.arrayOf(T.string),
  multiple: T.bool, // Attention : Finder must be able to handle it
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
