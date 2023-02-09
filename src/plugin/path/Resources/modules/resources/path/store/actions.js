import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

import {actions as resourceActions} from '#/main/core/resource/store'

import {constants} from '#/plugin/path/resources/path/constants'

export const STEP_ENABLE_NAVIGATION = 'STEP_ENABLE_NAVIGATION'
export const STEP_DISABLE_NAVIGATION = 'STEP_DISABLE_NAVIGATION'

export const ATTEMPT_LOAD = 'ATTEMPT_LOAD'
export const RESOURCE_EVALUATIONS_LOAD = 'RESOURCE_EVALUATIONS_LOAD'
export const STEP_UPDATE_PROGRESSION = 'STEP_UPDATE_PROGRESSION'

export const actions = {}

actions.enableNavigation = makeActionCreator(STEP_ENABLE_NAVIGATION)
actions.disableNavigation = makeActionCreator(STEP_DISABLE_NAVIGATION)
actions.loadAttempt = makeActionCreator(ATTEMPT_LOAD, 'attempt', 'resourceEvaluations')
actions.loadResourceEvaluations = makeActionCreator(RESOURCE_EVALUATIONS_LOAD, 'resourceEvaluations')
actions.updateStepProgression = makeActionCreator(STEP_UPDATE_PROGRESSION, 'stepId', 'status')

actions.updateProgression = (stepId, status = constants.STATUS_SEEN, silent = true) => ({
  [API_REQUEST]: {
    silent: silent,
    url: ['innova_path_progression_update', {id: stepId}],
    request: {
      method: 'PUT',
      body: JSON.stringify({status: status})
    },
    success: (data, dispatch) => {
      dispatch(resourceActions.updateUserEvaluation(data.userEvaluation))
      dispatch(actions.updateStepProgression(data.userProgression.stepId, data.userProgression.status))
    }
  }
})

actions.getAttempt = (pathId) => ({
  [API_REQUEST]: {
    url: ['innova_path_current_attempt', {id: pathId}],
    success: (response, dispatch) => dispatch(actions.loadAttempt(response.attempt, response.resourceEvaluations))
  }
})
