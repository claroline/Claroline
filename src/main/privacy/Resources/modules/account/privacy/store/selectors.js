import {createSelector} from 'reselect'

const STORE_NAME = 'accountPrivacy'

const store = (state) => state[STORE_NAME]

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const fetch = createSelector(
  [store],
  (store) => store.privacyData
)

export const selectors = {
    STORE_NAME,
    store,
    loaded,
    fetch
}
