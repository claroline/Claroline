import cloneDeep from 'lodash/cloneDeep'

import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'

import {
  DOCUMENT_UPDATE
} from '#/plugin/drop-zone/resources/dropzone/store/actions'
import {
  MY_DROP_LOAD,
  MY_DROP_UPDATE,
  DOCUMENTS_ADD,
  DOCUMENT_REMOVE,
  PEER_DROP_LOAD,
  PEER_DROP_RESET,
  PEER_DROPS_INC,
  CURRENT_REVISION_ID_LOAD,
  REVISION_LOAD,
  REVISION_RESET,
  REVISION_DOCUMENT_REMOVE,
  REVISION_COMMENT_UPDATE,
  MY_DROP_COMMENT_UPDATE,
  MANAGER_DOCUMENTS_ADD
} from '#/plugin/drop-zone/resources/dropzone/player/store/actions'
import {
  DROP_UPDATE,
  CORRECTION_UPDATE
} from '#/plugin/drop-zone/resources/dropzone/correction/actions'

const reducer = {
  myDrop: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.myDrop,
    [MY_DROP_LOAD]: (state, action) => action.drop,
    [MY_DROP_UPDATE]: (state, action) => {
      return Object.assign({}, state, {[action.property]: action.value})
    },
    [DROP_UPDATE]: (state, action) => {
      return state && state.id === action.drop.id ? action.drop : state
    },
    [DOCUMENTS_ADD]: (state, action) => {
      // When adding a new document, all documents from previous revision is archived in the revision
      const documents = cloneDeep(state.documents.filter(d => !d.revision))
      action.documents.forEach(d => documents.push(d))

      return Object.assign({}, state, {documents: documents})
    },
    [DOCUMENT_UPDATE]: (state, action) => {
      const documents = cloneDeep(state.documents)
      const index = documents.findIndex(d => d.id === action.document.id)

      if (index > -1) {
        documents[index] = action.document
      }

      return Object.assign({}, state, {documents: documents})
    },
    [DOCUMENT_REMOVE]: (state, action) => {
      const documents = cloneDeep(state.documents)
      const index = documents.findIndex(d => d.id === action.documentId)

      if (index > -1) {
        documents.splice(index, 1)
      }

      return Object.assign({}, state, {documents: documents})
    },
    [CORRECTION_UPDATE]: (state, action) => {
      if (state && state.id === action.correction.drop) {
        const corrections = cloneDeep(state.corrections)
        const index = corrections.findIndex(c => c.id === action.correction.id)

        if (index > -1) {
          corrections[index] = action.correction
        } else {
          corrections.push(action.correction)
        }

        return Object.assign({}, state, {corrections: corrections})
      } else {
        return state
      }
    },
    [MY_DROP_COMMENT_UPDATE]: (state, action) => {
      const newComments = cloneDeep(state.comments)
      const commentIdx = newComments.findIndex(c => c.id === action.comment.id)

      if (-1 < commentIdx) {
        newComments[commentIdx] = action.comment
      } else {
        newComments.push(action.comment)
      }

      return Object.assign({}, state, {comments: newComments})
    }
  }),
  nbCorrections: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.nbCorrections,
    [PEER_DROPS_INC]: (state) => {
      return state + 1
    }
  }),
  peerDrop: makeReducer(null, {
    [CORRECTION_UPDATE]: (state, action) => {
      if (state && state.id === action.correction.drop) {
        const corrections = cloneDeep(state.corrections)
        const index = corrections.findIndex(c => c.id === action.correction.id)

        if (index > -1) {
          corrections[index] = action.correction
        } else {
          corrections.push(action.correction)
        }

        return Object.assign({}, state, {corrections: corrections})
      }

      return state
    },
    [PEER_DROP_LOAD]: (state, action) => action.drop,
    [PEER_DROP_RESET]: () => null,
    [DROP_UPDATE]: (state, action) => {
      return state && state.id === action.drop.id ? action.drop : state
    }
  }),
  myRevisions: makeListReducer(selectors.STORE_NAME+'.myRevisions', {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  revisions: makeListReducer(selectors.STORE_NAME+'.revisions', {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  revision: makeReducer(null, {
    [REVISION_LOAD]: (state, action) => action.revision,
    [REVISION_RESET]: () => null,
    [REVISION_COMMENT_UPDATE]: (state, action) => {
      const newComments = cloneDeep(state.comments)
      const commentIdx = newComments.findIndex(c => c.id === action.comment.id)

      if (-1 < commentIdx) {
        newComments[commentIdx] = action.comment
      } else {
        newComments.push(action.comment)
      }

      return Object.assign({}, state, {comments: newComments})
    },
    [MANAGER_DOCUMENTS_ADD]: (state, action) => {
      const newDocuments = cloneDeep(state.documents)
      action.documents.forEach(d => newDocuments.push(d))

      return Object.assign({}, state, {documents: newDocuments})
    },
    [REVISION_DOCUMENT_REMOVE]: (state, action) => {
      const newDocuments = cloneDeep(state.documents)
      const index = newDocuments.findIndex(d => d.id === action.documentId)

      if (index > -1) {
        newDocuments.splice(index, 1)
      }

      return Object.assign({}, state, {documents: newDocuments})
    }
  }),
  currentRevisionId: makeReducer(null, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.currentRevisionId,
    [CURRENT_REVISION_ID_LOAD]: (state, action) => action.revisionId
  })
}

export {
  reducer
}
