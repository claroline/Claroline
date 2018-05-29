import {makeReducer, combineReducers} from '#/main/core/scaffolding/reducer'
import {LOAD_DASHBOARD} from '#/main/core/tools/workspace/dashboard/actions'

const reducer = {
  workspaceId: makeReducer(null, {}),
  dashboard: combineReducers({
    loaded: makeReducer(false, {
      [LOAD_DASHBOARD] : () => true
    }),
    data: makeReducer({}, {
      [LOAD_DASHBOARD]: (state, action) => action.data
    })
  })
}

export {
  reducer
}
