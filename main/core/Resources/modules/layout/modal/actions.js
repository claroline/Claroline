import {makeActionCreator} from '#/main/core/utilities/redux'

export const MODAL_SHOW = 'MODAL_SHOW'
export const MODAL_FADE = 'MODAL_FADE'
export const MODAL_HIDE = 'MODAL_HIDE'

export const actions = {}

actions.showModal = makeActionCreator(MODAL_SHOW, 'modalType', 'modalProps')
actions.fadeModal = makeActionCreator(MODAL_FADE)
actions.hideModal = makeActionCreator(MODAL_HIDE)
