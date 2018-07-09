import {makeActionCreator} from '#/main/core/scaffolding/actions'

export const CURRENT_TAB = 'CURRENT_TAB'
export const actions = {}

actions.setCurrentTab = makeActionCreator(CURRENT_TAB, 'tab')
