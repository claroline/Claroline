import {createSelector} from 'reselect'

import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'
import {selectors as homeSelectors} from '#/main/core/tools/home/selectors'

const editorTabs = (state) => formSelectors.data(formSelectors.form(state, 'editor'))

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

export const selectors = {
  editorTabs,
  currentTab,
  currentTabIndex,
  widgets
}
