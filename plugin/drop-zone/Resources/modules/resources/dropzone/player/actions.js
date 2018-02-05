import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {API_REQUEST} from '#/main/core/api/actions'
import {navigate} from '#/main/core/router'

import {select} from '#/plugin/drop-zone/resources/dropzone/selectors'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'

export const MY_DROP_LOAD = 'MY_DROP_LOAD'
export const MY_DROP_UPDATE = 'MY_DROP_UPDATE'
export const DOCUMENTS_ADD = 'DOCUMENTS_ADD'
export const DOCUMENT_UPDATE = 'DOCUMENT_UPDATE'
export const DOCUMENT_REMOVE = 'DOCUMENT_REMOVE'
export const PEER_DROP_LOAD = 'PEER_DROP_LOAD'
export const PEER_DROP_RESET = 'PEER_DROP_RESET'
export const PEER_DROPS_INC = 'PEER_DROPS_INC'

export const actions = {}

actions.loadMyDrop = makeActionCreator(MY_DROP_LOAD, 'drop')
actions.updateMyDrop = makeActionCreator(MY_DROP_UPDATE, 'property', 'value')
actions.addDocuments = makeActionCreator(DOCUMENTS_ADD, 'documents')
actions.updateDocument = makeActionCreator(DOCUMENT_UPDATE, 'document')
actions.removeDocument = makeActionCreator(DOCUMENT_REMOVE, 'documentId')

actions.initializeMyDrop = (dropzoneId, teamId = null) => ({
  [API_REQUEST]: {
    url: ['claro_dropzone_drop_create', {id: dropzoneId, teamId: teamId}],
    request: {
      method: 'POST'
    },
    success: (data, dispatch) => {
      dispatch(actions.loadMyDrop(data))
      navigate('/my/drop')
    }
  }
})

actions.saveDocument = (dropId, documentType, documentData) => {
  const formData = new FormData()
  formData.append('dropData', documentData)

  return {
    [API_REQUEST]: {
      url: ['claro_dropzone_documents_add', {id: dropId, type: documentType}],
      request: {
        method: 'POST',
        body: formData
      },
      success: (data, dispatch) => dispatch(actions.addDocuments(data))
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
  const peerDrop = select.peerDrop(state)

  if (!peerDrop) {
    const dropzone = select.dropzone(state)
    const myTeamId = select.myTeamId(state)

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

actions.submitCorrection = (correctionId) => ({
  [API_REQUEST]: {
    url: ['claro_dropzone_correction_submit', {id: correctionId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(actions.incPeerDrop())
      dispatch(actions.resetPeerDrop())
      navigate('/')
    }
  }
})
