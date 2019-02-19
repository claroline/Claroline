import {makeActionCreator} from '#/main/app/store/actions'

export const PATH_ADD_STEP    = 'PATH_ADD_STEP'
export const PATH_COPY_STEP   = 'PATH_COPY_STEP'
export const PATH_MOVE_STEP   = 'PATH_MOVE_STEP'
export const PATH_REMOVE_STEP = 'PATH_REMOVE_STEP'

export const actions = {}

actions.addStep = makeActionCreator(PATH_ADD_STEP, 'step', 'parentId')
actions.copyStep = makeActionCreator(PATH_COPY_STEP, 'id', 'position')
actions.moveStep = makeActionCreator(PATH_MOVE_STEP, 'id', 'position')
actions.removeStep = makeActionCreator(PATH_REMOVE_STEP, 'id')
