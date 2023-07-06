import {makeActionCreator} from '#/main/app/store/actions'

export const PRIVACY_UPDATE_COUNTRY = 'PRIVACY_UPDATE_COUNTRY'

export const PRIVACY_UPDATE_TERMS = 'PRIVACY_UPDATE_TERMS'

export const PRIVACY_UPDATE_TERMS_ENABLED = 'PRIVACY_UPDATE_TERMS_ENABLED'

export const PRIVACY_UPDATE_DPO = 'PRIVACY_UPDATE_DPO'

export const actions = {}

actions.updateCountry = makeActionCreator(PRIVACY_UPDATE_COUNTRY, 'countryStorage')
actions.updateTermsOfService = makeActionCreator(PRIVACY_UPDATE_TERMS, 'termsOfService')
actions.updateTermsEnabled = makeActionCreator(PRIVACY_UPDATE_TERMS_ENABLED, 'termsOfServiceEnabled')
actions.updateDpo = makeActionCreator(PRIVACY_UPDATE_DPO, 'dpo')
