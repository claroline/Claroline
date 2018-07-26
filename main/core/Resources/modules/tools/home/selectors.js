import {createSelector} from 'reselect'
import isEmpty from 'lodash/isEmpty'
import intersection from 'lodash/intersection'

import {currentUser} from '#/main/core/user/current'

const currentTabId = (state) => state.currentTabId
const editable = (state) => state.editable
const editing = (state) => state.editing
const context = (state) => state.context
const tabs = (state) => state.tabs

const authenticatedUser = currentUser()

const currentTab = createSelector(
  [tabs, currentTabId],
  (tabs, currentTabId) => tabs.find(tab => currentTabId === tab.id)
)

const widgets = createSelector(
  [currentTab],
  (currentTab) => currentTab.widgets
)

const sortedTabs = createSelector(
  [tabs],
  (tabs) => tabs.sort((a,b) => a.position - b.position)
)

const visibleTabs = createSelector(
  [sortedTabs],
  (sortedTabs) => {
    const userRoles = authenticatedUser.roles.map(role => role.id)

    return sortedTabs
      .filter(tab => {
        if (isEmpty(tab.roles)) {
          return true
        } else {
          return 0 !== intersection(tab.roles, userRoles).length
        }
      })
  }
)

export const selectors = {
  currentTab,
  currentTabId,
  editable,
  editing,
  context,
  tabs,
  sortedTabs,
  visibleTabs,
  widgets
}
