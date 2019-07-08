import {makeActionCreator, makeInstanceActionCreator} from '#/main/app/store/actions'

// actions
export const TOOL_OPEN        = 'TOOL_OPEN'
export const TOOL_CLOSE       = 'TOOL_CLOSE'
export const TOOL_LOAD        = 'TOOL_LOAD'
export const TOOL_SET_LOADED  = 'TOOL_SET_LOADED'

// action creators
export const actions = {}

actions.load = makeInstanceActionCreator(TOOL_LOAD, 'toolData')
actions.setLoaded = makeActionCreator(TOOL_SET_LOADED)

actions.open = makeActionCreator(TOOL_OPEN, 'name', 'context', 'basePath')
actions.close = makeActionCreator(TOOL_CLOSE)
