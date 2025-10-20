import {createSelector} from 'reselect'

const STORE_NAME = 'authentication'
const FORM_NAME = STORE_NAME+'.form'

const store = (baseStore) => baseStore[STORE_NAME]

const oauthProviders = createSelector(
  [store],
  (store) => store.oauthProviders
)

const oauthClients = createSelector(
  [store],
  (store) => store.oauthClients
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  store,
  oauthProviders,
  oauthClients
}


