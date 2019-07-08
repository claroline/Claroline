import {createSelector} from 'reselect'

import {trans} from '#/main/app/intl/translation'

import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

const STORE_NAME = 'home'

const store = (state) => state[STORE_NAME]

const context = toolSelectors.context

const currentTabId = createSelector(
  [store],
  (store) => store.currentTabId
)
const editable = createSelector(
  [store],
  (store) => store.editable
)
const administration = createSelector(
  [store],
  (store) => store.administration
)
const editing = createSelector(
  [store],
  (store) => store.editing
)

const tabs = createSelector(
  [store],
  (store) => store.tabs || []
)

const currentTab = createSelector(
  [tabs, currentTabId],
  (tabs, currentTabId) => tabs.find(tab => currentTabId === tab.id)
)

const currentTabTitle = createSelector(
  [context, currentTab],
  (context, currentTab) => {
    if (currentTab) {
      return currentTab.longTitle
    }

    if (context.data && context.data.name) {
      return context.data.name
    }

    if ('desktop' === context.type) {
      return trans('desktop')
    }

    return trans('home')
  }
)

const widgets = createSelector(
  [currentTab],
  (currentTab) => currentTab ? (currentTab.widgets || []) : []
)

const sortedTabs = createSelector(
  [tabs],
  (tabs) => [].concat(tabs).sort((a,b) => a.position - b.position)
)

export const selectors = {
  STORE_NAME,
  sortedTabs,
  currentTab,
  currentTabId,
  currentTabTitle,
  editable,
  administration,
  editing,
  context,
  tabs,
  widgets
}
