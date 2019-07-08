import invariant from 'invariant'

import {makeActionCreator} from '#/main/app/store/actions'
import {makeId} from '#/main/core/scaffolding/id'

import {actions as overlayActions} from '#/main/app/overlays/store/actions'

// actions
export const MODAL_SHOW = 'MODAL_SHOW'
export const MODAL_FADE = 'MODAL_FADE'
export const MODAL_HIDE = 'MODAL_HIDE'

// action creators
export const actions = {}

actions.openModal = (modalId, modalType, modalProps = {}) => {
  invariant(!!modalId, 'modalId is required')
  invariant(!!modalType, 'modalType is required')

  return {
    type: MODAL_SHOW,
    modalId,
    modalType,
    modalProps
  }
}
actions.showModal = (modalType, modalProps) => (dispatch) => {
  const modalId = makeId()

  dispatch(overlayActions.showOverlay(modalId))
  dispatch(actions.openModal(modalId, modalType, modalProps))
}

actions.fadeModal = makeActionCreator(MODAL_FADE, 'modalId')
actions.closeModal = makeActionCreator(MODAL_HIDE, 'modalId')
actions.hideModal = (modalId) => (dispatch) => {
  invariant(!!modalId, 'modalId is required')

  dispatch(actions.closeModal(modalId))
  dispatch(overlayActions.hideOverlay(modalId))
}
