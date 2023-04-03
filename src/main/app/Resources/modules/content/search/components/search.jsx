import React from 'react'

import {implementPropTypes} from '#/main/app/prop-types'
import {isTypeEnabled} from '#/main/app/data/types'

import {SearchFull} from '#/main/app/content/search/components/full'
import {SearchUnified} from '#/main/app/content/search/components/unified'
import {Search as SearchTypes} from '#/main/app/content/search/prop-types'
import {constants} from '#/main/app/content/search/constants'

const Search = props => {
  // exclude disabled data types from the search
  const values = []

  const filters = props.available.filter(filter => {
    if (isTypeEnabled(filter.type)) {
      // grab filter value
      const filterValue = props.current.find(value => value.property === filter.name || value.property === filter.alias)
      if (filterValue) {
        values.push(filterValue)
      }

      return true
    }

    return false
  })

  // we keep filter values without column definition in order to keep the "hidden" filters work
  const otherFilters = props.current.filter(value => {
    const filterDef = props.available.find(filter => value.property === filter.name || value.property === filter.alias)
    if (!filterDef) {

      return true
    }
  })

  switch (props.mode) {
    case constants.SEARCH_FULL:
      return (
        <SearchFull
          {...props}
          available={filters}
          current={values}
          resetFilters={(userFilters) => props.resetFilters([].concat(userFilters, otherFilters))}
        />
      )

    case constants.SEARCH_UNIFIED:
      return (
        <SearchUnified
          {...props}
          available={filters}
          current={values}
          resetFilters={(userFilters) => props.resetFilters([].concat(userFilters, otherFilters))}
        />
      )
  }
}

implementPropTypes(Search, SearchTypes)

export {
  Search
}
