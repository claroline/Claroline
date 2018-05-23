import {makeReducer, combineReducers} from '#/main/core/scaffolding/reducer'
import {makePageReducer} from '#/main/core/layout/page/reducer'
import {LOAD_DASHBOARD} from '#/main/core/tools/workspace/dashboard/actions'

const reducer = makePageReducer([], {
  workspaceId: makeReducer(null, {}),
  dashboard: combineReducers({
    loaded: makeReducer(false, {
      [LOAD_DASHBOARD] : () => true
    }),
    data: makeReducer({}, {
      [LOAD_DASHBOARD]: (state, action) => action.data
    })
  })
})

export {reducer}