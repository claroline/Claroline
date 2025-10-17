import {createSelector} from 'reselect'

const STORE_NAME = 'locations'

const store = (state) => state[STORE_NAME]

const currentLocation = createSelector(
  [store],
  (store) => store.current
)

export const selectors = {
  STORE_NAME,
  currentLocation
}
