import {createSelector} from 'reselect'

const STORE_NAME = 'config'

const store = (state) => state[STORE_NAME]

const selfRegistration = createSelector(
  [store],
  (store) => store.selfRegistration
)

export const selectors = {
  STORE_NAME,

  store,
  selfRegistration
}
