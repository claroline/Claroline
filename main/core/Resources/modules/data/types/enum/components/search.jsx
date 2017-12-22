import React from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {DataSearch as DataSearchTypes} from '#/main/core/data/prop-types'

const EnumSearch = (props) =>
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
      }}
    >
      <option />
      {Object.keys(props.choices).map(value =>
        <option key={value} value={value}>{props.choices[value]}</option>
      )}
    </select>
  </span>

EnumSearch.propTypes = merge({}, DataSearchTypes.propTypes, {
  choices: T.object.isRequired
})

export {
  EnumSearch
}
