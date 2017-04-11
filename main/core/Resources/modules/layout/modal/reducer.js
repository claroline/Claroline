import {makeReducer} from '#/main/core/utilities/redux'
import {
  MODAL_SHOW,
  MODAL_FADE,
  MODAL_HIDE
} from './actions'

function showModal(currentState, action) {
  return {
    type: action.modalType,
    props: action.modalProps,
    fading: false
  }
}

function fadeModal(currentState) {
  return Object.assign({}, currentState, {
    fading: true
  })
}

function hideModal() {
  return {
    type: null,
    props: {},
    fading: false
  }
}

const reducer = makeReducer({
  type: null,
  props: {},
  fading: false
}, {
  [MODAL_SHOW]: showModal,
  [MODAL_FADE]: fadeModal,
  [MODAL_HIDE]: hideModal
})

export {reducer}
