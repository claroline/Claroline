import {createSelector} from 'reselect'

import {trans} from '#/main/app/intl/translation'
import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'
import {selectors as homeSelectors} from '#/plugin/home/tools/home/store/selectors'

import {flattenTabs} from '#/plugin/home/tools/home/utils'

const FORM_NAME = `${homeSelectors.STORE_NAME}.editor`

const editorTabs = (state) => {
  return [].concat(formSelectors.data(formSelectors.form(state, FORM_NAME)) || [])
    .sort((a, b) => a.position - b.position)
}

const currentTab = (state) => {
  const currentTabId = homeSelectors.currentTabId(state)
  const tabs = flattenTabs(formSelectors.data(formSelectors.form(state, FORM_NAME)) || [])

  return tabs.find(tab => currentTabId === tab.slug)
}

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

const readOnly = createSelector(
  [homeSelectors.context, homeSelectors.administration, currentTab],
  (context, administration, currentTab) => !currentTab || (currentTab.context === 'administration' &&
    context.type === 'desktop' && !administration)
)

export const selectors = {
  FORM_NAME,

  editorTabs,
  currentTab,
  currentTabTitle,
  readOnly
}
