import cloneDeep from 'lodash/cloneDeep'

import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'

import {
  DROP_UPDATE,
  CURRENT_DROP_LOAD,
  CURRENT_DROP_RESET,
  CURRENT_DROP_COMMENT_UPDATE,
  CORRECTOR_DROP_LOAD,
  CORRECTOR_DROP_RESET,
  CORRECTIONS_LOAD,
  CORRECTION_UPDATE,
  CORRECTION_REMOVE
} from '#/plugin/drop-zone/resources/dropzone/correction/actions'
import {
  DOCUMENT_UPDATE
} from '#/plugin/drop-zone/resources/dropzone/store/actions'

const currentDropReducer = makeReducer(null, {
  [CURRENT_DROP_LOAD]: (state, action) => {
    return action.drop
  },
  [CURRENT_DROP_RESET]: () => {
    return null
  },
  [DROP_UPDATE]: (state, action) => {
    return state && state.id === action.drop.id ? action.drop : state
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
  [CORRECTION_REMOVE]: (state, action) => {
    const corrections = cloneDeep(state.corrections)
    const index = corrections.findIndex(c => c.id === action.correctionId)

    if (index > -1) {
      corrections.splice(index, 1)
    }

    return Object.assign({}, state, {corrections: corrections})
  },
  [DOCUMENT_UPDATE]: (state, action) => {
    const documents = cloneDeep(state.documents)
    const index = documents.findIndex(d => d.id === action.document.id)

    if (index > -1) {
      documents[index] = action.document
    }

    return Object.assign({}, state, {documents: documents})
  },
  [CURRENT_DROP_COMMENT_UPDATE]: (state, action) => {
    const newComments = cloneDeep(state.comments)
    const commentIdx = newComments.findIndex(c => c.id === action.comment.id)

    if (-1 < commentIdx) {
      newComments[commentIdx] = action.comment
    } else {
      newComments.push(action.comment)
    }

    return Object.assign({}, state, {comments: newComments})
  }
})

const dropsReducer = makeReducer({}, {
  [DROP_UPDATE]: (state, action) => {
    const drops = cloneDeep(state)
    const index = drops.findIndex(d => d.id === action.drop.id)

    if (index > -1) {
      drops[index] =  action.drop
    }

    return drops
  }
})

const correctorDropReducer = makeReducer(null, {
  [CORRECTOR_DROP_LOAD]: (state, action) => {
    return action.drop
  },
  [CORRECTOR_DROP_RESET]: () => {
    return null
  }
})

const correctionsReducer = makeReducer(null, {
  [CORRECTIONS_LOAD]: (state, action) => {
    return action.corrections
  }
})

const reducer = {
  drops: makeListReducer(selectors.STORE_NAME+'.drops', {}, {
    data: dropsReducer,
    invalidated: makeReducer(false, {
      [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  currentDrop: currentDropReducer,
  correctorDrop: correctorDropReducer,
  corrections: correctionsReducer
}

export {
  reducer
}
