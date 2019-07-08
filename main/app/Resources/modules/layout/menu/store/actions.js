import {makeActionCreator} from '#/main/app/store/actions'

// actions
export const MENU_TOGGLE = 'MENU_TOGGLE'
export const MENU_CHANGE_SECTION = 'MENU_CHANGE_SECTION'

// action creators
export const actions = {}

actions.toggle = makeActionCreator(MENU_TOGGLE)
actions.changeSection = makeActionCreator(MENU_CHANGE_SECTION, 'section')
