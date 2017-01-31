import {MODAL_SHOW, MODAL_FADE, MODAL_HIDE} from './actions'

const initialModalState = {
  type: null,
  props: {},
  fading: false
}

export function reduceModal(modalState = initialModalState, action) {
  switch (action.type) {
    case MODAL_SHOW:
      return {
        type: action.modalType,
        props: action.modalProps,
        fading: false
      }
    case MODAL_FADE:
      return Object.assign({}, modalState, {fading: true})
    case MODAL_HIDE:
      return initialModalState
  }
  return modalState
}
