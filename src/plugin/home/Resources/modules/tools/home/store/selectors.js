import {createSelector} from 'reselect'

import {trans} from '#/main/app/intl/translation'

import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

const STORE_NAME = 'home'

const store = (state) => state[STORE_NAME]

const context = toolSelectors.context

const defaultTab = createSelector(
  [context],
  (context) => ({
    context: 'administration' === context.type ? 'admin' : context.type,
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
    parameters: {
      widgets: []
    }
  })
)

const currentTabId = createSelector(
  [store],
  (store) => store.currentTabId
)

const administration = createSelector(
  [store],
  (store) => store.administration
)

export const selectors = {
  STORE_NAME,

  store,
  defaultTab,
  currentTabId,
  administration,
  context
}
