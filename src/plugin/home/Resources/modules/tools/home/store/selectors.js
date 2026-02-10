import {createSelector} from 'reselect'

import {trans} from '#/main/app/intl/translation'

import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'
import {flattenTabs, getTabTitle} from '#/plugin/home/tools/home/utils'

const STORE_NAME = 'home'

const store = (state) => state[STORE_NAME]

const defaultTab = createSelector(
  [toolSelectors.context],
  (context) => ({
    type: 'widgets',
    class: 'Claroline\\HomeBundle\\Entity\\Type\\WidgetsTab',
    title: trans('home'),
    longTitle: trans('home'),
    slug: 'default',
    position: 0,
    restrictions: {
      hidden: false
    },
    workspace: context.data,
    parameters: {
      widgets: []
    }
  })
)

const currentTabId = createSelector(
  [store],
  (store) => store.currentTabId
)

const tabs = createSelector(
  [store],
  (store) => []
    .concat(store.tabs)
    .sort((a, b) => a.position - b.position)
)

const currentTab = createSelector(
  [tabs, currentTabId],
  (tabs, currentTabId) => {
    const flattened = flattenTabs(tabs)

    return flattened.find(tab => currentTabId === tab.slug)
  }
)

const currentTabTitle = createSelector(
  [toolSelectors.context, currentTab],
  (context, currentTab) => getTabTitle(context, currentTab)
)

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

// access restrictions selectors
const error = createSelector(
  [store],
  (store) => store.error
)

export const selectors = {
  STORE_NAME,

  store,
  defaultTab,
  currentTabId,
  tabs,
  currentTab,
  currentTabTitle,
  loaded,
  error
}
