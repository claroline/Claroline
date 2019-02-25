import {API_REQUEST} from '#/main/app/api'
import {actions as resourceActions} from '#/main/core/resource/store/actions'

export const actions = {}

actions.download = (resourceNode) => ({
  [API_REQUEST]: {
    url: ['claro_resource_download', {
      ids: [resourceNode.id]
    }],
    forceDownload: true,
    request: {
      method: 'GET'
    },
    error: (response, status, dispatch) => {
      switch(status) {
        case 500: dispatch(resourceActions.setServerErrors(response)); break
      }
    }
  }
})
