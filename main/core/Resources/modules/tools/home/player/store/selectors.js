import {createSelector} from 'reselect'

import {selectors as homeSelectors} from '#/main/core/tools/home/store/selectors'

const tabs = createSelector(
  [homeSelectors.sortedTabs],
  (sortedTabs) => sortedTabs.filter(tab => !tab.restrictions || !tab.restrictions.hidden)
)

export const selectors = {
  tabs
}
