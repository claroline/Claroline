import {API_REQUEST} from '#/main/app/api'

import {actions as layoutActions} from '#/main/app/layout/store/actions'

// action creators
export const actions = {}

actions.enableMaintenance = (message) => ({
  [API_REQUEST]: {
    url: ['apiv2_maintenance_enable'],
    request: {
      method: 'PUT',
      body: message
    },
    success: (response, dispatch) => dispatch(layoutActions.setMaintenance(true, response))
  }
})

actions.disableMaintenance = () => ({
  [API_REQUEST]: {
    url: ['apiv2_maintenance_disable'],
    request: {
      method: 'PUT'
    },
    success: (response, dispatch) => dispatch(layoutActions.setMaintenance(false, null))
  }
})
