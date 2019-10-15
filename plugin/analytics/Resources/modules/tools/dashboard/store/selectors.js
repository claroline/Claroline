import {createSelector} from 'reselect'

const STORE_NAME = 'dashboard'

const store = (state) => state[STORE_NAME]

const actions = createSelector(
  [store],
  (store) => store.actions
)

const chart = createSelector(
  [store],
  (store) => store.chart
)

const items = createSelector(
  [store],
  (store) => store.items
)

const levelMax = createSelector(
  [store],
  (store) => store.levelMax
)

const nbConnections = createSelector(
  [store],
  (store) => store.nbConnections
)

const analytics = createSelector(
  [store],
  (store) => store.analytics
)

const requirements = createSelector(
  [store],
  (store) => store.requirements
)

const currentRequirements = createSelector(
  [requirements],
  (requirements) => requirements.current
)

export const selectors = {
  STORE_NAME,
  store,
  actions,
  chart,
  items,
  levelMax,
  nbConnections,
  analytics,
  currentRequirements
}
