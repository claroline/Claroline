import {makeActionCreator} from '#/main/core/utilities/redux'

export const PAGE_FULLSCREEN_TOGGLE   = 'PAGE_FULLSCREEN_TOGGLE'

export const actions = {}

actions.toggleFullscreen = makeActionCreator(PAGE_FULLSCREEN_TOGGLE)
