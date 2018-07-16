import {createSelector} from 'reselect'

import {select as formSelectors} from '#/main/core/data/form/selectors'
import {selectors as homeSelectors} from '#/main/core/tools/home/selectors'

const editorTabs = (state) => formSelectors.data(formSelectors.form(state, 'editor')).tabs

const currentTabIndex = createSelector(
  [editorTabs, homeSelectors.currentTabId],
  (editorTabs, currentTabId) => editorTabs.findIndex(tab => currentTabId === tab.id)
)

const currentTab = createSelector(
  [editorTabs, currentTabIndex],
  (editorTabs, currentTabIndex) => editorTabs[currentTabIndex]
)

const widgets = createSelector(
  [currentTab],
  (currentTab) => currentTab.widgets
)

// const sortedEditorTabs = createSelector(
//   [editorTabs],
//   (editorTabs) => editorTabs.sort((a,b) => a.position - b.position)
// )


export const selectors = {
  editorTabs,
  currentTab,
  currentTabIndex,
  // sortedEditorTabs,
  widgets
}
