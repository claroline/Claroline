import get from 'lodash/get'
import {API_REQUEST} from '#/main/app/api'

import {actions as resourceActions} from '#/main/core/resource/store'
import {constants} from '#/main/evaluation/constants'

export const actions = {}

actions.updateProgression = (id, currentTime, totalTime) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_peertube_video_progression_update', {id: id, currentTime: currentTime, totalTime: totalTime}],
    request: {
      method: 'PUT'
    },
    success: (response) => {
      if (constants.EVALUATION_TERMINATED_STATUSES.includes(get(response.userEvaluation, 'status'))) {
        dispatch(resourceActions.triggerLifecycleAction('end'))
      }

      return dispatch(resourceActions.updateUserEvaluation(response.userEvaluation))
    }
  }
})
