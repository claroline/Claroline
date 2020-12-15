import {createSelector} from 'reselect'

const STORE_NAME = 'resourceDashboard'

const store = (state) => state[STORE_NAME]

const chart = createSelector(
  [store],
  (store) => store.chart
)

export const selectors = {
  STORE_NAME,
  store,

  chart
}
