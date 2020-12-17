import {createSelector} from 'reselect'

import {selectors as baseSelectors} from '#/plugin/rss/resources/rss-feed/store/selectors'

const items = createSelector(
  [baseSelectors.rssFeed],
  (rssFeed) => rssFeed.items || []
)

export const selectors = {
  items
}
