import {makeActionCreator} from '#/main/app/store/actions'

export const QUIZ_STEP_ADD    = 'QUIZ_STEP_ADD'
export const QUIZ_STEP_COPY   = 'QUIZ_STEP_COPY'
export const QUIZ_STEP_MOVE   = 'QUIZ_STEP_MOVE'
export const QUIZ_STEP_REMOVE = 'QUIZ_STEP_REMOVE'

export const actions = {}

actions.addStep = makeActionCreator(QUIZ_STEP_ADD, 'step')
actions.copyStep = makeActionCreator(QUIZ_STEP_COPY, 'id', 'position')
actions.moveStep = makeActionCreator(QUIZ_STEP_MOVE, 'id', 'position')
actions.removeStep = makeActionCreator(QUIZ_STEP_REMOVE, 'id')
