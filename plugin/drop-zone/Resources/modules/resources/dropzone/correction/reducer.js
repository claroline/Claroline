import cloneDeep from 'lodash/cloneDeep'

import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'

import {
  DROP_UPDATE,
  CURRENT_DROP_LOAD,
  CURRENT_DROP_RESET,
  CORRECTOR_DROP_LOAD,
  CORRECTOR_DROP_RESET,
  CORRECTIONS_LOAD,
  CORRECTION_UPDATE,
  CORRECTION_REMOVE
} from '#/plugin/drop-zone/resources/dropzone/correction/actions'
import {
  DOCUMENT_UPDATE
} from '#/plugin/drop-zone/resources/dropzone/player/actions'

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
  drops: makeListReducer('drops', {}, {data: dropsReducer}),
  currentDrop: currentDropReducer,
  correctorDrop: correctorDropReducer,
  corrections: correctionsReducer
}

export {
  reducer
}
