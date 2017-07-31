import cloneDeep from 'lodash/cloneDeep'

import {makeReducer} from '#/main/core/utilities/redux'

import {
  WORKSPACES_LOAD,
  WORKSPACE_ADD_MANAGER,
  WORKSPACE_REMOVE_MANAGER
} from './actions'

const handlers = {
  [WORKSPACES_LOAD]: (state, action) => {
    return {
      data: action.workspaces,
      totalResults: action.total
    }
  },
  [WORKSPACE_ADD_MANAGER]: (state, action) => {
    state = cloneDeep(state)
    const workspace = state.data.find(workspace => workspace.id === action.workspace.id)
    workspace.managers.push(action.user)

    return state
  },
  [WORKSPACE_REMOVE_MANAGER]: (state, action) => {
    state = cloneDeep(state)
    const workspace = state.data.find(workspace => workspace.id === action.workspace.id)
    workspace.managers.splice(workspace.managers.findIndex(manager => manager.id === action.user.id), 1)

    return state
  }
}

const reducer = makeReducer({
  data: [],
  totalResults: 0
}, handlers)

export {
  reducer
}
