import {makeActionCreator} from '#/main/app/store/actions'

// actions
export const SIDEBAR_OPEN  = 'SIDEBAR_OPEN'
export const SIDEBAR_CLOSE = 'SIDEBAR_CLOSE'

// action creators
export const actions = {}

// Toolbar & Sidebar
actions.openSidebar = makeActionCreator(SIDEBAR_OPEN, 'toolName')
actions.closeSidebar = makeActionCreator(SIDEBAR_CLOSE)
