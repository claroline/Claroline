import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

const actions = {}

const PARAMETERS_LOAD = 'PARAMETERS_LOAD'

actions.loadParameters = makeActionCreator(PARAMETERS_LOAD, 'parameters')

actions.saveParameters = (parameters) => ({
  [API_REQUEST]: {
    url: ['apiv2_parameters_update'],
    request: {
      body: JSON.stringify(parameters),
      method: 'PUT'
    },
    success: (data, dispatch) => dispatch(actions.loadParameters(data))
  }
})

export {
  actions,
  PARAMETERS_LOAD
}