import {makeActionCreator} from '#/main/app/store/actions'

export const SWITCH_MODE = 'SWITCH_MODE'
    
export const actions = {}

actions.switchMode = makeActionCreator(SWITCH_MODE, 'mode')