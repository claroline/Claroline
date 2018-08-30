import {createSelector} from 'reselect'

import {selectors as contentSelectors} from '#/main/core/widget/content/store'

const STORE_NAME = 'list' // where the DataList will be mounted

const display = createSelector(
  [contentSelectors.parameters],
  (parameters) => parameters.display || []
)

const availableDisplays = createSelector(
  [contentSelectors.parameters],
  (parameters) => parameters.availableDisplays || []
)

const availableFilters = createSelector(
  [contentSelectors.parameters],
  (parameters) => parameters.availableFilters || []
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

export const selectors = {
  STORE_NAME,
  display,
  availableDisplays,
  availableFilters,
  availableSort,
  displayedColumns,
  availableColumns
}
