import {makeActionCreator} from '#/main/app/store/actions'

export const AUTHENTICATION_OAUTH_CLIENT_ADD = 'AUTHENTICATION_OAUTH_CLIENT_ADD'
export const AUTHENTICATION_OAUTH_CLIENT_UPDATE = 'AUTHENTICATION_OAUTH_CLIENT_UPDATE'
export const AUTHENTICATION_OAUTH_CLIENT_DELETE = 'AUTHENTICATION_OAUTH_CLIENT_DELETE'

export const actions = {}

actions.addOauthClient = makeActionCreator(AUTHENTICATION_OAUTH_CLIENT_ADD, 'client')
actions.updateOauthClient = makeActionCreator(AUTHENTICATION_OAUTH_CLIENT_UPDATE, 'client')
actions.deleteOauthClient = makeActionCreator(AUTHENTICATION_OAUTH_CLIENT_DELETE, 'client')
