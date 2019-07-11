import {createSelector} from 'reselect'

const STORE_NAME = 'templates_management'

const store = (state) => state[STORE_NAME]

const locales = createSelector(
  [store],
  (store) => store.locales
)

const defaultLocale = createSelector(
  [store],
  (store) => store.defaultLocale
)

export const selectors = {
  STORE_NAME,
  store,
  locales,
  defaultLocale
}
