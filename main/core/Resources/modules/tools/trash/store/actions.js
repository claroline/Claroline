import {API_REQUEST} from '#/main/app/api'
import {url} from '#/main/app/api/router'
import {actions as listActions} from '#/main/app/content/list/store/actions'

export const actions = {}

actions.restore = (nodes) => ({
  [API_REQUEST]: {
    url: url(['claro_resource_collection_action', {action: 'restore'}], {ids: nodes.map(node => node.id)}),
    request: {
      method: 'POST'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('resources'))
    }
  }
})
