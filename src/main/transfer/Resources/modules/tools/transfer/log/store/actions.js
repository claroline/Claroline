import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const LOG_REFRESH = 'LOG_REFRESH'
export const LOG_RESET = 'LOG_RESET'

export const actions = {}

actions.refresh = makeActionCreator(LOG_REFRESH, 'content')
actions.reset =  makeActionCreator(LOG_REFRESH)

actions.load = (transferId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_transfer_import_log', {id: transferId}],
    success: (response) => dispatch(actions.refresh(response))
  }
})
