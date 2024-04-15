import get from 'lodash/get'

import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const TOOL_RIGHTS_LOAD = 'TOOL_RIGHTS_LOAD'

export const actions = {}

actions.loadRights = makeActionCreator(TOOL_RIGHTS_LOAD, 'rights')

actions.fetchRights = (toolName, contextType, contextId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_tool_get_rights', {name: toolName, context: contextType, contextId: contextId}],
    success: (response) => dispatch(actions.loadRights(response))
  }
})
