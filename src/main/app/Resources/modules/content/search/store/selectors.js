import get from 'lodash/get'
import isArray from 'lodash/isArray'
import isEmpty from 'lodash/isEmpty'

// retrieves a search instance in the store
const search = (state, searchName) => get(state, searchName)

const text = (searchState) => searchState.text

const filters = (searchState) => searchState.filters

const hasSearch = (searchState) => {
  const currentText = text(searchState)
  if (!isEmpty(currentText)) {
    return true
  }

  const currentFilters = filters(searchState)
  if (!isEmpty(currentFilters)) {
    return true
  }

  return false
}

const queryString = (searchState) => {
  const queryParams = []

  // add filters
  const searchText = text(searchState)
  if (searchText) {
    queryParams.push(`q=${encodeURIComponent(searchText)}`)
  }

  const currentFilters = filters(searchState)
  if (!isEmpty(currentFilters)) {
    currentFilters.map(filter => {
      if (isArray(filter.value)) {
        filter.value.map(arrValue =>
          queryParams.push(`filters[${filter.property}][]=${encodeURIComponent(arrValue)}`)
        )
      } else {
        queryParams.push(`filters[${filter.property}]=${encodeURIComponent(filter.value)}`)
      }
    })
  }

  return queryParams.join('&')
}

export const selectors = {
  search,
  text,
  filters,
  hasSearch,
  queryString
}
