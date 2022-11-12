import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'

// actions
export const MAINTENANCE_SET = 'MAINTENANCE_SET'

// action creators
export const actions = {}

actions.setMaintenance = makeActionCreator(MAINTENANCE_SET, 'enabled', 'message')

actions.enableMaintenance = (message) => ({
  [API_REQUEST]: {
    url: ['apiv2_maintenance_enable'],
    request: {
      method: 'PUT',
      body: message
    },
    success: (response, dispatch) => dispatch(actions.setMaintenance(true, response))
  }
})

actions.disableMaintenance = () => ({
  [API_REQUEST]: {
    url: ['apiv2_maintenance_disable'],
    request: {
      method: 'PUT'
    },
    success: (response, dispatch) => dispatch(actions.setMaintenance(false, null))
  }
})

actions.extend = () => ({
  [API_REQUEST]: {
    url: ['apiv2_platform_extend'],
    request: {
      method: 'PUT'
    },
    success: () => window.location.href = url(['claro_index'])
  }
})

actions.disablePlatform = () => ({
  [API_REQUEST]: {
    url: ['apiv2_platform_disable'],
    request: {
      method: 'PUT'
    },
    success: () => true
  }
})
