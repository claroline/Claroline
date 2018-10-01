import {createSelector} from 'reselect'

import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'
import {selectors as homeSelectors} from '#/main/core/tools/home/store/selectors'

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

const sortedEditorTabs = createSelector(
  [editorTabs],
  (editorTabs) => editorTabs.sort((a,b) => a.position - b.position)
)

const readOnly = createSelector(
  [homeSelectors.context, homeSelectors.administration, currentTab],
  (context, administration, currentTab) => currentTab.type === 'administration' &&
    context.type === 'desktop' && !administration
)

export const selectors = {
  editorTabs,
  currentTab,
  currentTabIndex,
  widgets,
  sortedEditorTabs,
  readOnly
}
