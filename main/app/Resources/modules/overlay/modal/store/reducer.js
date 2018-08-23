import cloneDeep from 'lodash/cloneDeep'

import {makeReducer} from '#/main/app/store/reducer'

import {makeId} from '#/main/core/scaffolding/id'

import {
  MODAL_SHOW,
  MODAL_FADE,
  MODAL_HIDE
} from '#/main/app/overlay/modal/store/actions'

const reducer = makeReducer([], {
  [MODAL_SHOW]: (state, action) => [{
    id: makeId(),
    type: action.modalType,
    props: action.modalProps,
    fading: false
  }].concat(state),

  [MODAL_FADE]: (state, action) => {
    const newState = cloneDeep(state)

    const position = newState.findIndex(modal => modal.id === action.modalId)
    if (-1 !== position) {
      newState[position].fading = true
    }

    return newState
  },

  [MODAL_HIDE]: (state, action) => {
    const newState = cloneDeep(state)

    const position = newState.findIndex(modal => modal.id === action.modalId)
    if (-1 !== position) {
      newState.splice(position, 1)
    }

    return newState
  }
})

export {
  reducer
}
