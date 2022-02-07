import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {PATHS_DATA_LOAD} from '#/plugin/path/analytics/workspace/path/store/actions'

const reducer = combineReducers({
  tracking: makeReducer([], {
    [PATHS_DATA_LOAD]: (state, action) => action.tracking
  })
})

export {
  reducer
}
