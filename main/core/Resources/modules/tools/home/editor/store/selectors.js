import {createSelector} from 'reselect'

import {trans} from '#/main/app/intl/translation'
import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'
import {selectors as homeSelectors} from '#/main/core/tools/home/store/selectors'

const FORM_NAME = `${homeSelectors.STORE_NAME}.editor`

const editorTabs = (state) => {
  const definedTabs = [].concat(formSelectors.data(formSelectors.form(state, FORM_NAME)) || [])
    .sort((a, b) => a.position - b.position)

  if (0 === definedTabs.length) {
    definedTabs.push(homeSelectors.defaultTab(state))
  }

  return definedTabs
}

const currentTabIndex = createSelector(
  [editorTabs, homeSelectors.currentTabId],
  (editorTabs, currentTabId) => editorTabs.findIndex(tab => currentTabId === tab.slug)
)

const currentTab = createSelector(
  [editorTabs, currentTabIndex],
  (editorTabs, currentTabIndex) => editorTabs[currentTabIndex]
)

const currentTabTitle = createSelector(
  [homeSelectors.context, currentTab],
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
  (currentTab) => currentTab ? currentTab.widgets : []
)

const readOnly = createSelector(
  [homeSelectors.context, homeSelectors.administration, currentTab],
  (context, administration, currentTab) => !currentTab || (currentTab.type === 'administration' &&
    context.type === 'desktop' && !administration)
)

export const selectors = {
  FORM_NAME,

  editorTabs,
  currentTab,
  currentTabIndex,
  currentTabTitle,
  widgets,
  readOnly
}
