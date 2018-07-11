import {createSelector} from 'reselect'

import {select as formSelectors} from '#/main/core/data/form/selectors'
import {select as homeSelectors} from '#/main/core/tools/home/selectors'

const editorData = (state) => formSelectors.data(formSelectors.form(state, 'editor'))

const currentTabIndex = createSelector(
  [editorData, homeSelectors.currentTabId],
  (editorData, currentTabId) => editorData.findIndex(tab => currentTabId === tab.id)
)

const currentTab = createSelector(
  [editorData, currentTabIndex],
  (editorData, currentTabIndex) => editorData[currentTabIndex]
)

const widgets = createSelector(
  [currentTab],
  (currentTab) => currentTab.widgets
)

const sortedTabs = createSelector(
  [editorData],
  (editorData) => editorData.sort((a,b) => a.position - b.position)
)


export const select = {
  currentTab,
  editorData,
  currentTabIndex,
  sortedTabs,
  widgets
}
