import React, {PropTypes as T} from 'react'

const SearchFilter = props =>
  <div className="search-filter">
    <span className="search-filter-prop">
      {props.property}
    </span>
    <span className="search-filter-value">
      {props.value}

      <span className="fa fa-times" />
    </span>
  </div>

SearchFilter.propTypes = {
  property: T.string.isRequired,
  value: T.any.isRequired
}

/**
 * Data list search box.
 *
 * @param props
 * @constructor
 */
const ListSearch = props =>
  <div className="list-search">
    <div className="search-filters">
      {props.filters.active.map(activeFilter =>
        <SearchFilter property={activeFilter.property} value={activeFilter.value} />
      )}
      <input type="text" className="form-control search-control" placeholder="Search in the list" />
    </div>

    <span className="search-icon" aria-hidden="true">
      <span className="fa fa-fw fa-search" />
    </span>
  </div>

ListSearch.propTypes = {
  filters: T.shape({
    available: T.arrayOf(T.string).isRequired,
    active: T.arrayOf(T.shape({
      property: T.string.isRequired,
      value: T.any.isRequired
    })).isRequired,
    onChange: T.func.isRequired
  }).isRequired
}

export {
  ListSearch
}
