import {createSelector} from 'reselect'

import {selectors as contentSelectors} from '#/main/core/widget/content/store'

const STORE_NAME = 'list' // where the DataList will be mounted

const display = createSelector(
  [contentSelectors.parameters],
  (parameters) => parameters.display
)

const count = createSelector(
  [contentSelectors.parameters],
  (parameters) => parameters.count || false
)

const availableDisplays = createSelector(
  [contentSelectors.parameters],
  (parameters) => parameters.availableDisplays || []
)

const availableFilters = createSelector(
  [contentSelectors.parameters],
  (parameters) => parameters.availableFilters || []
)

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

const availableSort = createSelector(
  [contentSelectors.parameters],
  (parameters) => parameters.availableSort || []
)

const displayedColumns = createSelector(
  [contentSelectors.parameters],
  (parameters) => parameters.columns || []
)

const availableColumns = createSelector(
  [contentSelectors.parameters],
  (parameters) => parameters.availableColumns || []
)

const paginated = createSelector(
  [contentSelectors.parameters],
  (parameters) => parameters.paginated || false
)

const pageSize = createSelector(
  [contentSelectors.parameters],
  (parameters) => parameters.pageSize || false
)

const availablePageSizes = createSelector(
  [contentSelectors.parameters],
  (parameters) => parameters.availablePageSizes || []
)

export const selectors = {
  STORE_NAME,
  display,
  count,
  paginated,
  pageSize,
  availablePageSizes,
  availableDisplays,
  availableFilters,
  filters,
  sorting,
  availableSort,
  displayedColumns,
  availableColumns
}
