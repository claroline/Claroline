import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {LOAD_DASHBOARD} from '#/main/core/workspace/analytics/actions'

const reducer = {
  workspace: makeReducer(null, {}),
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
