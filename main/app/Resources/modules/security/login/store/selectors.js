import {createSelector} from 'reselect'

import {selectors as configSelectors} from '#/main/app/config/store/selectors'

const STORE_NAME = 'login'
const FORM_NAME = `${STORE_NAME}`

const sso = (state) => configSelectors.param(state, 'sso')

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
