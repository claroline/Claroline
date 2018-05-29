import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/core/scaffolding/actions'

export const LOG_REFRESH = 'LOG_REFRESH'
export const LOG_RESET = 'LOG_RESET'
export const actions = {}

actions.refresh = makeActionCreator(LOG_REFRESH, 'content')
actions.reset =  makeActionCreator(LOG_REFRESH)

actions.load = (file) => {
  return {
    [API_REQUEST]: {
      url: ['apiv2_logger_get', {name: file}],
      success: (response, dispatch) => {
        dispatch(actions.refresh(response))
      }
    }
  }
}
