import {makeActionCreator} from '#/main/core/scaffolding/actions'

export const API_REQUEST = 'API_REQUEST'

export const REQUEST_SEND     = 'REQUEST_SEND'
export const RESPONSE_RECEIVE = 'RESPONSE_RECEIVE'

export const actions = {}

actions.sendRequest = makeActionCreator(REQUEST_SEND, 'apiRequest')
actions.receiveResponse = makeActionCreator(RESPONSE_RECEIVE, 'apiRequest', 'status', 'statusText')
