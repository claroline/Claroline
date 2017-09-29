import React from 'react'
import {PropTypes as T} from 'prop-types'

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
        props.updateSearch(props.options.enum[e.target.value])
        e.preventDefault()
      }}
    >
      <option />
      {Object.keys(props.options.enum).map(value =>
        <option key={value} value={value}>{props.options.enum[value]}</option>
      )}
    </select>
  </span>

EnumSearch.propTypes = {
  search: T.string.isRequired,
  isValid: T.bool.isRequired,
  updateSearch: T.func.isRequired,
  options: T.object.isRequired
}

export {
  EnumSearch
}
