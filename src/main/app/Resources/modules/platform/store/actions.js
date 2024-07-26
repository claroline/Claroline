import {API_REQUEST, url} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

// action names
export const PLATFORM_SET_CURRENT_ORGANIZATION = 'PLATFORM_SET_CURRENT_ORGANIZATION'

// action creators
export const actions = {}

actions.setCurrentOrganizations = makeActionCreator(PLATFORM_SET_CURRENT_ORGANIZATION, 'organization')

actions.extend = () => ({
  [API_REQUEST]: {
    url: ['apiv2_platform_extend'],
    request: {
      method: 'PUT'
    },
    success: () => window.location.href = url(['claro_index'])
  }
})
