import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {API_REQUEST} from '#/main/core/api/actions'

import {actions as evaluationActions} from '#/main/core/resource/evaluation/actions'

import {constants} from '#/plugin/path/resources/path/constants'

export const STEP_ENABLE_NAVIGATION = 'STEP_ENABLE_NAVIGATION'
export const STEP_DISABLE_NAVIGATION = 'STEP_DISABLE_NAVIGATION'

export const STEP_UPDATE_PROGRESSION = 'STEP_UPDATE_PROGRESSION'

export const actions = {}

actions.enableNavigation = makeActionCreator(STEP_ENABLE_NAVIGATION)
actions.disableNavigation = makeActionCreator(STEP_DISABLE_NAVIGATION)

actions.updateStepProgression = makeActionCreator(STEP_UPDATE_PROGRESSION, 'stepId', 'status')

actions.updateProgression = (stepId, status = constants.STATUS_SEEN) => ({
  [API_REQUEST]: {
    silent: true,
    url: ['innova_path_progression_update', {id: stepId}],
    request: {
      method: 'PUT',
      body: JSON.stringify({status: status})
    },
    success: (data, dispatch) => {
      dispatch(evaluationActions.updateUserEvaluation(data['evaluation']))
      dispatch(actions.updateStepProgression(data['userProgression']['stepId'], data['userProgression']['status']))
    }
  }
})
