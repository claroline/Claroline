import {createSelector} from 'reselect'

const STORE_NAME = 'pathDashboard'

const store = (state) => state[STORE_NAME]

const tracking = createSelector(
  [store],
  (store) => store.tracking
)

export const selectors = {
  STORE_NAME,
  tracking
}
