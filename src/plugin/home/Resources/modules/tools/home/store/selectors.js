import {createSelector} from 'reselect'

import {trans} from '#/main/app/intl/translation'

import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

const STORE_NAME = 'home'

const store = (state) => state[STORE_NAME]

const context = toolSelectors.context

const defaultTab = createSelector(
  [context],
  (context) => ({
    type: 'widgets',
    class: 'Claroline\\HomeBundle\\Entity\\Type\\WidgetsTab',
    title: trans('home'),
    longTitle: trans('home'),
    slug: 'default',
    centerTitle: false,
    position: 0,
    restrictions: {
      hidden: false
    },
    workspace: context.data,
    parameters: {
      widgets: []
    }
  })
)

const currentTabId = createSelector(
  [store],
  (store) => store.currentTabId
)

export const selectors = {
  STORE_NAME,

  store,
  defaultTab,
  currentTabId,
  context
}
