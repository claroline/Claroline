import {createSelector} from 'reselect'

const STORE_NAME = 'hevinci_url'

const resource = (state) => state[STORE_NAME]

const url = createSelector(
  [resource],
  (resource) => resource.url
)

export const selectors = {
  STORE_NAME,
  url
}
