import {createSelector} from 'reselect'

const STORE_NAME = 'platform_dashboard'

const store = (state) => state[STORE_NAME]

const log = createSelector(
  [store],
  (store) => store.log
)

const actions = createSelector(
  [store],
  (store) => store.actions
)

const chart = createSelector(
  [store],
  (store) => store.chart
)

const overview = createSelector(
  [store],
  (store) => store.overview
)

const audience = createSelector(
  [store],
  (store) => store.audience
)

const resources = createSelector(
  [store],
  (store) => store.resources
)

const widgets = createSelector(
  [store],
  (store) => store.widgets
)

const topActions = createSelector(
  [store],
  (store) => store.topActions
)

const connections = createSelector(
  [store],
  (store) => store.connections
)

export const selectors = {
  STORE_NAME,
  store,
  log,
  actions,
  chart,
  overview,
  audience,
  resources,
  widgets,
  topActions,
  connections
}
