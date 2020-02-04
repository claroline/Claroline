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
    forceDownload: true,
    request: {
      method: 'GET'
    }
  }
})

actions.createComment = (comment) => ({
  [API_REQUEST]: {
    url: ['apiv2_resourcecomment_create'],
    request: {
      method: 'POST',
      body: JSON.stringify(comment)
    },
    success: (data, dispatch) => dispatch(resourceActions.addResourceComment(data))
  }
})

actions.editComment = (comment) => ({
  [API_REQUEST]: {
    url: ['apiv2_resourcecomment_update', {id: comment.id}],
    request: {
      method: 'PUT',
      body: JSON.stringify(comment)
    },
    success: (data, dispatch) => dispatch(resourceActions.updateResourceComment(data))
  }
})

actions.deleteComment = (commentId) => ({
  [API_REQUEST]: {
    url: ['apiv2_resourcecomment_delete_bulk', {ids: [commentId]}],
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => dispatch(resourceActions.removeResourceComment(commentId))
  }
})

export {
  actions,
  FILE_PROP_UPDATE
}