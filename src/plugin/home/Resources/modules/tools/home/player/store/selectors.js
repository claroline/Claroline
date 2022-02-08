import {createSelector} from 'reselect'

import {flattenTabs, getTabTitle} from '#/plugin/home/tools/home/utils'
import {selectors as homeSelectors} from '#/plugin/home/tools/home/store/selectors'

const tabs = (state) => {
  return []
    .concat(homeSelectors.store(state).tabs)
    .sort((a,b) => a.position - b.position)
}

const currentTab = createSelector(
  [tabs, homeSelectors.currentTabId],
  (tabs, currentTabId) => {
    const flattened = flattenTabs(tabs)

    return flattened.find(tab => currentTabId === tab.slug)
  }
)

const currentTabTitle = createSelector(
  [homeSelectors.context, currentTab],
  (context, currentTab) => getTabTitle(context, currentTab)
)

export const selectors = {
  tabs,
  currentTab,
  currentTabTitle
}
