import {createSelector} from 'reselect'

const STORE_NAME = 'main_settings'

const store = (state) => state[STORE_NAME]

const availableLocales = createSelector(
  [store],
  (store) => store.availableLocales
)

const lockedParameters = createSelector(
  [store],
  (store) => store.lockedParameters
)


const locales = createSelector(
  [parameters],
  (parameters) => parameters.locales
)

export const selectors = {
  STORE_NAME,

  store,
  availableLocales,
  lockedParameters,
  parameters,
  locales
}
