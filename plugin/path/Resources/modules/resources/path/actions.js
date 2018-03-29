import {makeActionCreator} from '#/main/core/scaffolding/actions'

export const SUMMARY_PIN_TOGGLE  = 'SUMMARY_PIN_TOGGLE'
export const SUMMARY_OPEN_TOGGLE = 'SUMMARY_OPEN_TOGGLE'

export const STEP_UPDATE_USER_PROGRESSION_STATUS = 'STEP_UPDATE_USER_PROGRESSION_STATUS'

export const actions = {}

actions.toggleSummaryPin = makeActionCreator(SUMMARY_PIN_TOGGLE)
actions.toggleSummaryOpen = makeActionCreator(SUMMARY_OPEN_TOGGLE)

actions.updateStepUserProgressionStatus = makeActionCreator(STEP_UPDATE_USER_PROGRESSION_STATUS, 'stepId', 'status')
