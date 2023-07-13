import {createSelector} from 'reselect'

const STORE_NAME = 'accountPrivacy'

const store = (state) => state[STORE_NAME]

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const privacyParameters = createSelector(
  [store],
  (store) => store.privacyParameters
)

export const selectors = {
  STORE_NAME,
  loaded,
  privacyParameters
}
