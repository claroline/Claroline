import {createSelector} from 'reselect'

import {selectors as dashboardSelectors} from '#/plugin/analytics/tools/dashboard/store/selectors'

const STORE_NAME = dashboardSelectors.STORE_NAME + '.path'

const store = (state) => state[dashboardSelectors.STORE_NAME]

const path = createSelector(
  [store],
  (store) => store.path
)

const tracking = createSelector(
  [path],
  (path) => path.tracking
)

export const selectors = {
  STORE_NAME,
  path,
  tracking
}
