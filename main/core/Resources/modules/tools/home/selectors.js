import {createSelector} from 'reselect'

const currentTabId = (state) => state.currentTabId
const editable = (state) => state.editable
const administration = (state) => state.administration
const editing = (state) => state.editing
const context = (state) => state.context
const tabs = (state) => state.tabs


const currentTab = createSelector(
  [tabs, currentTabId],
  (tabs, currentTabId) => tabs.find(tab => currentTabId === tab.id)
)

const widgets = createSelector(
  [currentTab],
  (currentTab) => currentTab.widgets
)


export const selectors = {
  currentTab,
  currentTabId,
  editable,
  administration,
  editing,
  context,
  tabs,
  widgets
}
