import get from 'lodash/get'

// retrieves a search instance in the store
const search = (state, searchName) => get(state, searchName)

const filters = (searchState) => searchState

const queryString = (searchState) => {
  const queryParams = []

  // add filters
  const currentFilters = filters(searchState)
  if (0 < currentFilters.length) {
    currentFilters.map(filter => {
      queryParams.push(`filters[${filter.property}]=${encodeURIComponent(filter.value)}`)
    })
  }

  return queryParams.join('&')
}

export const selectors = {
  search,
  filters,
  queryString
}
