import {API_REQUEST} from '#/main/app/api'

import {actions as mainActions} from '#/plugin/cursus/administration/cursus/store'

const actions = {}

actions.saveParameters = (parameters) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_parameters_update'],
    request: {
      body: JSON.stringify(parameters),
      method: 'PUT'
    },
    success: (data, dispatch) => dispatch(mainActions.loadParameters(data))
  }
})

export {
  actions
}