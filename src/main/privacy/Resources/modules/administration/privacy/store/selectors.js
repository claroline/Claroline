import {createSelector} from 'reselect'

const STORE_NAME = 'privacy'

const store = (state) => state[STORE_NAME]

const dpo = createSelector(
  [store],
  (store) => store.dpo
)

const termsOfService = createSelector(
  [store],
  (store) => store.termsOfService
)

const termsOfServiceEnabled = createSelector(
  [store],
  (store) => store.termsOfServiceEnabled
)

const countryStorage = createSelector(
  [store],
  (store) => store.countryStorage
)

export const selectors = {
  STORE_NAME,

  store,
  dpo,
  termsOfService,
  termsOfServiceEnabled,
  countryStorage
}
