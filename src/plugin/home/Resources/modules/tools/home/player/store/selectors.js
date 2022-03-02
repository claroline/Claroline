import {createSelector} from 'reselect'

import {flattenTabs, getTabTitle} from '#/plugin/home/tools/home/utils'
import {selectors as homeSelectors} from '#/plugin/home/tools/home/store/selectors'
import isEmpty from 'lodash/isEmpty'

const store = createSelector(
  [homeSelectors.store],
  (homeStore) => homeStore.player
)

const tabs = (state) => {
  return []
    .concat(homeSelectors.store(state).tabs)
    .sort((a, b) => a.position - b.position)
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

const managed = createSelector(
  [store],
  (store) => store.managed
)

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

// access restrictions selectors
const accessErrors = createSelector(
  [store],
  (store) => !store.accessErrors.dismissed && !isEmpty(store.accessErrors.details) ? store.accessErrors.details : {}
)

export const selectors = {
  tabs,
  currentTab,
  currentTabTitle,
  managed,
  loaded,
  accessErrors
}
