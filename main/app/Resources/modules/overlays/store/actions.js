import {makeActionCreator} from '#/main/app/store/actions'

export const OVERLAY_SHOW = 'OVERLAY_SHOW'
export const OVERLAY_HIDE = 'OVERLAY_HIDE'

export const actions = {}

actions.showOverlay = makeActionCreator(OVERLAY_SHOW, 'overlayId')
actions.hideOverlay = makeActionCreator(OVERLAY_HIDE, 'overlayId')
