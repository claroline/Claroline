import {API_REQUEST} from '#/main/app/api'

import {actions as resourceActions} from '#/main/core/resource/store'

export const actions = {}

actions.updateProgression = (id, currentTime, totalTime) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_peertube_video_progression_update', {id: id, currentTime: currentTime, totalTime: totalTime}],
    request: {
      method: 'PUT'
    },
    success: (response) => dispatch(resourceActions.updateUserEvaluation(response.userEvaluation))
  }
})
