import React from 'react'
import {PropTypes as T} from 'prop-types'

const EnumSearch = (props) =>
  <span className="enum-filter">
    <select onChange={(e) => {props.updateSearch(props.options.enum[e.target.value])}}>
      {Object.keys(props.options.enum).map(value => <option value={value}>{props.options.enum[value]}</option>)}
    </select>
  </span>

EnumSearch.propTypes = {
  search: T.string.isRequired,
  isValid: T.bool.isRequired,
  updateSearch: T.func.isRequired,
  options: T.object.isRequired
}

export {EnumSearch}
