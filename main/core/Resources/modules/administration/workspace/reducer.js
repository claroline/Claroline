import cloneDeep from 'lodash/cloneDeep'

import {makeReducer} from '#/main/core/utilities/redux'
import {makeListReducer} from '#/main/core/layout/list/reducer'

import {
  WORKSPACE_ADD_MANAGER,
  WORKSPACE_REMOVE_MANAGER
} from './actions'

const workspaceReducer = makeReducer([], {
  [WORKSPACE_ADD_MANAGER]: (state, action) => {
    state = cloneDeep(state)
    const workspace = state.find(workspace => workspace.id === action.workspace.id)
    workspace.managers.push(action.user)

    return state
  },

  [WORKSPACE_REMOVE_MANAGER]: (state, action) => {
    state = cloneDeep(state)
    const workspace = state.find(workspace => workspace.id === action.workspace.id)
    workspace.managers.splice(workspace.managers.findIndex(manager => manager.id === action.user.id), 1)

    return state
  }
})

const reducer = makeListReducer({
  data: workspaceReducer
})

export {
  reducer
}
