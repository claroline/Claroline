import {makeActionCreator} from '#/main/app/store/actions'

export const STEP_ADD = 'STEP_ADD'
export const STEP_COPY = 'STEP_COPY'
export const STEP_MOVE = 'STEP_MOVE'
export const STEP_REMOVE = 'STEP_REMOVE'

export const actions = {}

actions.addStep = makeActionCreator(STEP_ADD, 'parentId')
actions.copyStep = makeActionCreator(STEP_COPY, 'id', 'position')
actions.moveStep = makeActionCreator(STEP_MOVE, 'id', 'position')
actions.removeStep = makeActionCreator(STEP_REMOVE, 'id')
