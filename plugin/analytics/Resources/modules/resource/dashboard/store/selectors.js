import {createSelector} from 'reselect'

const STORE_NAME = 'resourceDashboard'

const store = (state) => state[STORE_NAME]

const dashboard = createSelector(
  [store],
  (resource) => resource.dashboard
)

export const selectors = {
  STORE_NAME,

  dashboard
}
