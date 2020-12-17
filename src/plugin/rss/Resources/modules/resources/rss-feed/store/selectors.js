import {createSelector} from 'reselect'

const STORE_NAME = 'rss_feed'

const resource = (state) => state[STORE_NAME]

const rssFeed = createSelector(
  [resource],
  (resource) => resource.rssFeed
)

export const selectors = {
  STORE_NAME,
  rssFeed
}
