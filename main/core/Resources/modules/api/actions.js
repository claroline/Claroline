import { makeActionCreator } from '#/main/core/utilities/redux'

// TODO : add public file upload here (see quiz objects upload)

export const REQUEST_SEND     = 'REQUEST_SEND'
export const RESPONSE_RECEIVE = 'RECEIVE_RESPONSE'

export const REQUESTS_INCREMENT = 'REQUESTS_INCREMENT'
export const REQUESTS_DECREMENT = 'REQUESTS_DECREMENT'

export const actions = {}

actions.incrementRequests = makeActionCreator(REQUESTS_INCREMENT)
actions.decrementRequests = makeActionCreator(REQUESTS_DECREMENT)

actions.receiveResponse = makeActionCreator(RESPONSE_RECEIVE, 'response')
