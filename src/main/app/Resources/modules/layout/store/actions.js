import {API_REQUEST, url} from '#/main/app/api'

// action creators
export const actions = {}

actions.extend = () => ({
  [API_REQUEST]: {
    url: ['apiv2_platform_extend'],
    request: {
      method: 'PUT'
    },
    success: () => window.location.href = url(['claro_index'])
  }
})
