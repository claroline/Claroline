import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/core/data/prop-types'

const ChoiceSearch = (props) =>
  <span className="enum-filter">
    <select
      value={props.search}
      className="form-control input-sm"
      onClick={e => {
        e.preventDefault()
        e.stopPropagation()
      }}
      onChange={e => {
        props.updateSearch(e.target.value)
        e.preventDefault()
        e.stopPropagation()
      }}
    >
      <option />
      {Object.keys(props.choices).map(value =>
        <option key={value} value={value}>{props.choices[value]}</option>
      )}
    </select>
  </span>

implementPropTypes(ChoiceSearch, DataSearchTypes, {
  choices: T.object.isRequired
})

export {
  ChoiceSearch
}
