import {createSelector} from 'reselect'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'

import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

const STORE_NAME = 'home'

const store = (state) => state[STORE_NAME]

const context = toolSelectors.context

const defaultTab = createSelector(
  [context],
  (context) => ({
    id: makeId(),
    type: 'administration' === context.type ? 'admin' : context.type,
    title: trans('home'),
    longTitle: trans('home'),
    slug: 'default',
    centerTitle: false,
    position: 0,
    restrictions: {
      hidden: false
    },
    widgets: []
  })
)

const currentTabId = createSelector(
  [store],
  (store) => store.currentTabId
)

const editable = createSelector(
  [store],
  (store) => store.editable
)

const administration = createSelector(
  [store],
  (store) => store.administration
)

const desktopAdmin = createSelector(
  [store],
  (store) => store.desktopAdmin
)

export const selectors = {
  STORE_NAME,

  store,
  defaultTab,
  currentTabId,
  editable,
  administration,
  desktopAdmin,
  context
}
