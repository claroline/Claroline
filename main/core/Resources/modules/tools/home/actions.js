import {makeActionCreator} from '#/main/app/store/actions'

export const CURRENT_TAB = 'CURRENT_TAB'
export const actions = {}

actions.setCurrentTab = makeActionCreator(CURRENT_TAB, 'tab')
