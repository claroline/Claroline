import {makeActionCreator} from '#/main/app/store/actions'

// actions
export const DIRECTORY_CHANGE = 'DIRECTORY_CHANGE'

// actions creators
export const actions = {}

actions.changeDirectory = makeActionCreator(DIRECTORY_CHANGE, 'directoryNode')
