import {makeActionCreator} from '#/main/app/store/actions'

// actions
export const EXPLORER_SET_CURRENT = 'EXPLORER_SET_CURRENT'

// actions creators
export const actions = {}

actions.setCurrent = makeActionCreator(EXPLORER_SET_CURRENT, 'current', 'filters')
