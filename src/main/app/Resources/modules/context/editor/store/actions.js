import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const CONTEXT_LOAD_AVAILABLE_TOOLS = 'CONTEXT_LOAD_AVAILABLE_TOOLS'

export const actions = {}

actions.loadAvailableTools = makeActionCreator(CONTEXT_LOAD_AVAILABLE_TOOLS, 'tools')

actions.fetchAvailableTools = (contextName, contextId = null) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['claro_context_get_available_tools', {context: contextName, contextId: contextId ? contextId : 'null'}],
    success: (response) => dispatch(actions.loadAvailableTools(response))
  }
})
