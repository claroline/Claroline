import {makeActionCreator} from '#/main/core/utilities/redux'

export const MESSAGE_RESET = 'MESSAGE_RESET'
export const MESSAGE_UPDATE = 'MESSAGE_UPDATE'

export const actions = {}

actions.resetMessage = makeActionCreator(MESSAGE_RESET)
actions.updateMessage = makeActionCreator(MESSAGE_UPDATE, 'content', 'status')