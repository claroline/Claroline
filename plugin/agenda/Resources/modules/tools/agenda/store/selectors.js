import {createSelector} from 'reselect'
import moment from 'moment'

const STORE_NAME = 'agenda_'

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

export const selectors = {
  STORE_NAME,

  view,
  referenceDateStr,
  referenceDate
}
