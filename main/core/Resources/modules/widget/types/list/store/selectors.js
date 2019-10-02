import {createSelector} from 'reselect'

import {selectors as contentSelectors} from '#/main/core/widget/content/store'

const STORE_NAME = 'list' // where the DataList will be mounted

const filters = createSelector(
  [contentSelectors.parameters],
  (parameters) => parameters.filters || []
)

const sorting = createSelector(
  [contentSelectors.parameters],
  (parameters) => {
    if (parameters.sorting) {
      const reverse = 0 === parameters.sorting.indexOf('-')

      return {
        property: reverse ? parameters.sorting.replace('-', '') : parameters.sorting,
        direction: reverse ? -1 : 1
      }
    }

    return {
      property: null,
      direction: 0
    }
  }
)

const pageSize = createSelector(
  [contentSelectors.parameters],
  (parameters) => parameters.pageSize
)

export const selectors = {
  STORE_NAME,
  pageSize,
  filters,
  sorting
}
