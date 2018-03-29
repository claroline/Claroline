import {API_REQUEST} from '#/main/core/api/actions'

import {actions as evaluationActions} from '#/main/core/resource/evaluation/actions'

import {constants} from '#/plugin/path/resources/path/constants'
import {actions as pathActions} from '#/plugin/path/resources/path/actions'

export const actions = {}

actions.updateProgression = (stepId, status = constants.STATUS_SEEN) => ({
  [API_REQUEST]: {
    silent: true,
    url: ['innova_path_step_progression_update', {id: stepId}],
    request: {
      method: 'PUT',
      body: JSON.stringify({status: status})
    },
    success: (data, dispatch) => {
      dispatch(evaluationActions.updateUserEvaluation(data['evaluation']))
      dispatch(pathActions.updateStepUserProgressionStatus(data['userProgression']['stepId'], data['userProgression']['status']))
    }
  }
})
