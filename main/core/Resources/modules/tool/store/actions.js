import invariant from 'invariant'

import {constants} from '#/main/core/tool/constants'

// actions
export const TOOL_SET_CONTEXT = 'TOOL_SET_CONTEXT'

// action creators
export const actions = {}

actions.setContext = (contextType, contextData = null) => {
  invariant(contextType, 'contextType is required')

  const tools = Object.keys(constants.TOOL_TYPES)
  invariant(-1 !== tools.indexOf(contextType), `contextType is invalid. Allowed : ${tools.join(', ')}.`)

  return {
    type: TOOL_SET_CONTEXT,
    contextType,
    contextData
  }
}
