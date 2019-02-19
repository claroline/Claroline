import {makeActionCreator} from '#/main/app/store/actions'

export const QUIZ_ADD_STEP    = 'QUIZ_ADD_STEP'
export const QUIZ_COPY_STEP   = 'QUIZ_COPY_STEP'
export const QUIZ_MOVE_STEP   = 'QUIZ_MOVE_STEP'
export const QUIZ_REMOVE_STEP = 'QUIZ_REMOVE_STEP'

export const actions = {}

actions.addStep = makeActionCreator(QUIZ_ADD_STEP, 'step')
actions.copyStep = makeActionCreator(QUIZ_COPY_STEP, 'id', 'position')
actions.moveStep = makeActionCreator(QUIZ_MOVE_STEP, 'id', 'position')
actions.removeStep = makeActionCreator(QUIZ_REMOVE_STEP, 'id')
