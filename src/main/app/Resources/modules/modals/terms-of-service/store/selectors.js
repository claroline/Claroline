import {createSelector} from 'reselect'

const STORE_NAME = 'termsOfService'

const store = (state) => state[STORE_NAME]

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const content = createSelector(
  [store],
  (store) => store.content
)

export const selectors = {
  STORE_NAME,

  loaded,
  content
}
