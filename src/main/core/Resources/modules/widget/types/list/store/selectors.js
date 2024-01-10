import {createSelector} from 'reselect'

import {selectors as contentSelectors} from '#/main/core/widget/content/store'
import {parseSortBy} from '#/main/app/content/list/utils'

const STORE_NAME = 'list' // where the DataList will be mounted

const filters = createSelector(
  [contentSelectors.parameters],
  (parameters) => parameters.filters || []
)

const sorting = createSelector(
  [contentSelectors.parameters],
  (parameters) => parseSortBy(parameters.sorting)
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
