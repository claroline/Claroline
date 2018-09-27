import {makeActionCreator} from '#/main/app/store/actions'

export const SET_RIGHTS_RECURSIVE = 'SET_RIGHTS_RECURSIVE'
export const actions = {}

actions.setRecursive = makeActionCreator(SET_RIGHTS_RECURSIVE, 'recursiveEnabled')
