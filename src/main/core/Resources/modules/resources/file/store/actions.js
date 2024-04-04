import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'
import {actions as resourceActions} from '#/main/core/resource/store/actions'

const FILE_PROP_UPDATE = 'FILE_PROP_UPDATE'

const actions = {}

actions.updateFileProp = makeActionCreator(FILE_PROP_UPDATE, 'prop', 'value')

actions.download = (resourceNode) => ({
  [API_REQUEST]: {
    url: ['claro_resource_download', {
      ids: [resourceNode.id]
    }],
    request: {
      method: 'GET'
    }
  }
})

export {
  actions,
  FILE_PROP_UPDATE
}