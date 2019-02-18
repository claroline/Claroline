import {createSelector} from 'reselect'

const STORE_NAME = 'resource'

const resource = (state) => state[STORE_NAME]

const rssFeed = createSelector(
  [resource],
  (resource) => resource.rssFeed
)

export const selectors = {
  STORE_NAME,
  rssFeed
}
