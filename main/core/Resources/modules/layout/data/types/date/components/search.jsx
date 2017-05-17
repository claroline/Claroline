import React from 'react'
import {PropTypes as T} from 'prop-types'

const DateSearch = props =>
  <span className="date-filter">
    {props.isValid &&
      <span className="available-filter-value">{props.search}</span>
    }
    &nbsp;
    <button type="button" className="btn btn-sm btn-filter">
      <span className="fa fa-fw fa-calendar" />
    </button>
  </span>

DateSearch.propTypes = {
  search: T.string.isRequired,
  isValid: T.bool.isRequired,
  updateSearch: T.func.isRequired
}

export {DateSearch}
