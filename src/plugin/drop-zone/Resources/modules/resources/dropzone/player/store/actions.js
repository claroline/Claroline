import merge from 'lodash/merge'

import {makeActionCreator} from '#/main/app/store/actions'
import {actions as listActions} from '#/main/app/content/list/store'
import {API_REQUEST} from '#/main/app/api'

import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'
import {actions as correctionActions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'

export const MY_DROP_LOAD = 'MY_DROP_LOAD'
export const MY_DROP_UPDATE = 'MY_DROP_UPDATE'
export const DOCUMENTS_ADD = 'DOCUMENTS_ADD'
export const DOCUMENT_REMOVE = 'DOCUMENT_REMOVE'
export const PEER_DROP_LOAD = 'PEER_DROP_LOAD'
export const PEER_DROP_RESET = 'PEER_DROP_RESET'
export const PEER_DROPS_INC = 'PEER_DROPS_INC'
export const CURRENT_REVISION_ID_LOAD = 'CURRENT_REVISION_ID_LOAD'
export const REVISION_LOAD = 'REVISION_LOAD'
export const REVISION_RESET = 'REVISION_RESET'
export const REVISION_COMMENT_UPDATE = 'REVISION_COMMENT_UPDATE'
export const REVISION_DOCUMENT_REMOVE = 'REVISION_DOCUMENT_REMOVE'
export const MY_DROP_COMMENT_UPDATE = 'MY_DROP_COMMENT_UPDATE'
export const MANAGER_DOCUMENTS_ADD = 'MANAGER_DOCUMENTS_ADD'

export const actions = {}

actions.loadMyDrop = makeActionCreator(MY_DROP_LOAD, 'drop')
actions.updateMyDrop = makeActionCreator(MY_DROP_UPDATE, 'property', 'value')
actions.addDocuments = makeActionCreator(DOCUMENTS_ADD, 'documents')
actions.removeDocument = makeActionCreator(DOCUMENT_REMOVE, 'documentId')

actions.initializeMyDrop = (dropzoneId, teamId = null, navigate, path) => ({
  [API_REQUEST]: {
    url: ['claro_dropzone_drop_create', {id: dropzoneId, teamId: teamId}],
    request: {
      method: 'POST'
    },
    success: (data, dispatch) => {
      dispatch(actions.loadMyDrop(data))
      navigate(`${path}/my/drop`)
    }
  }
})

actions.saveDocument = (dropId, documentType, documentData) => {
  const formData = new FormData()
  if (constants.DOCUMENT_TYPE_FILE === documentType) {
    formData.append('dropData', documentData)
  } else {
    formData.append('dropData', JSON.stringify(documentData))
  }

  formData.append('fileName', 'test')
  formData.append('sourceType', 'uploadedfile')

  return {
    [API_REQUEST]: {
      url: ['claro_dropzone_documents_add', {id: dropId, type: documentType}],
      request: {
        method: 'POST',
        body: formData,
        headers: new Headers({
          //no Content type for automatic detection of boundaries.
          'X-Requested-With': 'XMLHttpRequest'
        })
      },
      success: (data, dispatch) => {
        dispatch(actions.addDocuments(data))
        dispatch(actions.loadCurrentRevisionId(null))
        dispatch(actions.resetRevision())
      }
    }
  }
}

actions.deleteDocument = (documentId) => ({
  [API_REQUEST]: {
    url: ['claro_dropzone_document_delete', {id: documentId}],
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => dispatch(actions.removeDocument(documentId))
  }
})

actions.submitDrop = (dropId) => ({
  [API_REQUEST]: {
    url: ['claro_dropzone_drop_submit', {id: dropId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => dispatch(actions.loadMyDrop(data))
  }
})

actions.fetchPeerDrop = () => (dispatch, getState) => {
  const state = getState()
  const peerDrop = selectors.peerDrop(state)

  if (!peerDrop) {
    const dropzone = selectors.dropzone(state)
    const myTeamId = selectors.myTeamId(state)

    if (dropzone.parameters.dropType === constants.DROP_TYPE_USER) {
      dispatch({
        [API_REQUEST]: {
          url: ['claro_dropzone_peer_drop_fetch', {id: dropzone.id}],
          success: (data, dispatch) => {
            if (data && data.id) {
              dispatch(actions.loadPeerDrop(data))
            }
          }
        }
      })
    } else if (dropzone.parameters.dropType === constants.DROP_TYPE_TEAM && myTeamId) {
      dispatch({
        [API_REQUEST]: {
          url: ['claro_dropzone_team_peer_drop_fetch', {id: dropzone.id, teamId: myTeamId}],
          success: (data, dispatch) => {
            if (data && data.id) {
              dispatch(actions.loadPeerDrop(data))
            }
          }
        }
      })
    }
  }
}

actions.loadPeerDrop = makeActionCreator(PEER_DROP_LOAD, 'drop')
actions.resetPeerDrop = makeActionCreator(PEER_DROP_RESET)
actions.incPeerDrop = makeActionCreator(PEER_DROPS_INC)

actions.submitCorrection = (correctionId, navigate, path) => ({
  [API_REQUEST]: {
    url: ['claro_dropzone_correction_submit', {id: correctionId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(actions.incPeerDrop())
      dispatch(actions.resetPeerDrop())
      navigate(path)
    }
  }
})

actions.submitDropForRevision = (dropId) => ({
  [API_REQUEST]: {
    url: ['claro_dropzone_drop_submit_for_revision', {id: dropId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(actions.loadMyDrop(data.drop))
      dispatch(actions.loadCurrentRevisionId(data.revision.id))
      dispatch(actions.loadRevision(data.revision))
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.myRevisions'))
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.revisions'))
    }
  }
})

actions.fetchRevision = (revisionId) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: ['apiv2_droprevision_get', {id: revisionId}],
      success: (data, dispatch) => {
        if (data && data.id) {
          dispatch(actions.loadRevision(data))
        }
      }
    }
  })
}

actions.fetchDropFromRevision = (revisionId) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: ['claro_dropzone_drop_from_revision_get', {id: revisionId}],
      success: (data, dispatch) => {
        if (data && data.id) {
          dispatch(correctionActions.loadCurrentDrop(data))
        }
      }
    }
  })
}

actions.loadCurrentRevisionId = makeActionCreator(CURRENT_REVISION_ID_LOAD, 'revisionId')
actions.loadRevision = makeActionCreator(REVISION_LOAD, 'revision')
actions.resetRevision = makeActionCreator(REVISION_RESET)
actions.updateRevisionComment = makeActionCreator(REVISION_COMMENT_UPDATE, 'comment')
actions.updateMyDropComment = makeActionCreator(MY_DROP_COMMENT_UPDATE, 'comment')
actions.removeRevisionDocument = makeActionCreator(REVISION_DOCUMENT_REMOVE, 'documentId')

actions.saveRevisionComment = (revisionId, comment) => ({
  [API_REQUEST]: {
    url: comment.id ? ['apiv2_revisioncomment_update', {id: comment.id}] : ['apiv2_revisioncomment_create'],
    request: {
      method: comment.id ? 'PUT' : 'POST',
      body: JSON.stringify(merge({}, comment, {
        revision: {id: revisionId}
      }))
    },
    success: (data, dispatch) => dispatch(actions.updateRevisionComment(data))
  }
})

actions.saveDropComment = (dropId, comment, myDrop = false) => ({
  [API_REQUEST]: {
    url: comment.id ? ['apiv2_dropcomment_update', {id: comment.id}] : ['apiv2_dropcomment_create'],
    request: {
      method: comment.id ? 'PUT' : 'POST',
      body: JSON.stringify(merge({}, comment, {
        drop: {id: dropId}
      }))
    },
    success: (data, dispatch) => myDrop?
      dispatch(actions.updateMyDropComment(data)) :
      dispatch(correctionActions.updateCurrentDropComment(data))
  }
})

actions.addManagerDocuments = makeActionCreator(MANAGER_DOCUMENTS_ADD, 'documents')

actions.saveManagerDocument = (dropId, revisionId, documentType, documentData) => {
  const formData = new FormData()
  formData.append('dropData', documentData)
  formData.append('fileName', 'test')
  formData.append('sourceType', 'uploadedfile')
  return {
    [API_REQUEST]: {
      url: ['claro_dropzone_manager_documents_add', {id: dropId, revision: revisionId, type: documentType}],
      request: {
        method: 'POST',
        body: formData,
        headers: new Headers({
          //no Content type for automatic detection of boundaries.
          'X-Requested-With': 'XMLHttpRequest'
        })
      },
      success: (data, dispatch) => dispatch(actions.addManagerDocuments(data))
    }
  }
}

actions.deleteManagerDocument = (documentId) => ({
  [API_REQUEST]: {
    url: ['claro_dropzone_manager_document_delete', {id: documentId}],
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => dispatch(actions.removeRevisionDocument(documentId))
  }
})