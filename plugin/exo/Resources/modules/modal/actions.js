import {makeActionCreator} from './../utils/actions'

export const MODAL_SHOW = 'MODAL_SHOW'
export const MODAL_FADE = 'MODAL_FADE'
export const MODAL_HIDE = 'MODAL_HIDE'

export const showModal = makeActionCreator(MODAL_SHOW, 'modalType', 'modalProps')
export const fadeModal = makeActionCreator(MODAL_FADE)
export const hideModal = makeActionCreator(MODAL_HIDE)
