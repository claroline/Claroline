import {API_REQUEST} from '#/main/app/api'

import {actions as resourceActions} from '#/main/core/resource/store'

export const actions = {}

actions.updateProgression = (id, currentPage, totalPage) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_pdf_progression_update', {id: id, page: currentPage, total: totalPage}],
    request: {
      method: 'PUT'
    },
    success: (response) => dispatch(resourceActions.updateUserEvaluation(response.userEvaluation))
  }
})
