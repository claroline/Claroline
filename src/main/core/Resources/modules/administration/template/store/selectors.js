import {createSelector} from 'reselect'

import {selectors as configSelectors} from '#/main/app/config/store/selectors'

const STORE_NAME = 'templates'

const store = (state) => state[STORE_NAME]

const currentTemplate = createSelector(
  [store],
  (store) => store.current || {}
)

const templateType = createSelector(
  [currentTemplate],
  (currentTemplate) => currentTemplate.type
)

const templates = createSelector(
  [currentTemplate],
  (currentTemplate) => currentTemplate && currentTemplate.templates ? currentTemplate.templates.data : []
)

const locales = (state) => configSelectors.param(state, 'locale.available')

const defaultLocale = (state) => configSelectors.param(state, 'locale.current')

export const selectors = {
  STORE_NAME,

  store,
  templateType,
  templates,
  locales,
  defaultLocale
}
