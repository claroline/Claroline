import {createSelector} from 'reselect'

import {trans} from '#/main/app/intl/translation'

const currentTabId = (state) => state.currentTabId
const editable = (state) => state.editable
const administration = (state) => state.administration
const editing = (state) => state.editing
const context = (state) => state.currentContext
const tabs = (state) => state.tabs

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
