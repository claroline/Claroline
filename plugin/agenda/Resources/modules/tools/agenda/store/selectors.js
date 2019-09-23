import {createSelector} from 'reselect'
import moment from 'moment'

const STORE_NAME = 'agenda'

const store = (state) => state[STORE_NAME]

const view = createSelector(
  [store],
  (store) => store.view
)

const referenceDateStr = createSelector(
  [store],
  (store) => store.referenceDate
)

const referenceDate = createSelector(
  [referenceDateStr],
  (referenceDateStr) => moment(referenceDateStr)
)

const types = createSelector(
  [store],
  (store) => store.types
)

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const events = createSelector(
  [store],
  (store) => store.events
)

export const selectors = {
  STORE_NAME,

  view,
  referenceDateStr,
  referenceDate,
  types,

  loaded,
  events
}
