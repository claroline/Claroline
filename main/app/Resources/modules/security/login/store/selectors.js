import {createSelector} from 'reselect'

const STORE_NAME = 'login'
const FORM_NAME = `${STORE_NAME}.form`

const store = (state) => state[STORE_NAME]

const sso = createSelector(
  [store],
  (store) => store.sso
)

const primarySso = createSelector(
  [sso],
  (sso) => sso.find(sso => sso.primary)
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,

  sso,
  primarySso
}
