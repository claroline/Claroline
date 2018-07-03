import invariant from 'invariant'

import {makeActionCreator} from '#/main/app/store/actions'

// actions
export const MODAL_SHOW = 'MODAL_SHOW'
export const MODAL_FADE = 'MODAL_FADE'
export const MODAL_HIDE = 'MODAL_HIDE'

// action creators
export const actions = {}

actions.showModal = (modalType, modalProps = {}) => {
  invariant(!!modalType, 'modalType is required')

  return {
    type: MODAL_SHOW,
    modalType,
    modalProps
  }
}

actions.fadeModal = makeActionCreator(MODAL_FADE)
actions.hideModal = makeActionCreator(MODAL_HIDE)
