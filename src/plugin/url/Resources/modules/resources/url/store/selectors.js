import {createSelector} from 'reselect'

const STORE_NAME = 'hevinci_url'

const store = (state) => state[STORE_NAME]

const url = createSelector(
  [store],
  (store) => store.resource
)

export const selectors = {
  STORE_NAME,
  url
}
