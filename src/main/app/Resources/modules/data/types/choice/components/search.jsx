import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/app/data/types/prop-types'

const ChoiceSearch = (props) =>
  <select
    value={props.search || undefined}
    className="data-filter choice-filter form-control input-sm"
    onClick={e => {
      e.preventDefault()
      e.stopPropagation()
    }}
    onChange={e => {
      props.updateSearch(e.target.value)
      e.preventDefault()
      e.stopPropagation()
    }}

    disabled={props.disabled}
  >
    <option>{props.placeholder}</option>

    {Object.keys(props.choices).map(value =>
      <option key={value} value={value}>{props.choices[value]}</option>
    )}
  </select>

implementPropTypes(ChoiceSearch, DataSearchTypes, {
  choices: T.object.isRequired
})

export {
  ChoiceSearch
}
