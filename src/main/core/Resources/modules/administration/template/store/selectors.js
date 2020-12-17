import {selectors as configSelectors} from '#/main/app/config/store/selectors'

const STORE_NAME = 'templates_management'

const store = (state) => state[STORE_NAME]

const locales = (state) => configSelectors.param(state, 'locale.available')

const defaultLocale = (state) => configSelectors.param(state, 'locale.current')

export const selectors = {
  STORE_NAME,

  store,
  locales,
  defaultLocale
}
