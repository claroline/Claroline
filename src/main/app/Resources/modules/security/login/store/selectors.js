import {selectors as configSelectors} from '#/main/app/config/store/selectors'

const STORE_NAME = 'login'
const FORM_NAME = `${STORE_NAME}`

const internalAccount = (state) => configSelectors.param(state, 'authentication.internalAccount', false)
const sso = (state) => configSelectors.param(state, 'authentication.sso', [])

export const selectors = {
  STORE_NAME,
  FORM_NAME,

  sso,
  internalAccount
}
