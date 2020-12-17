import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {PATHS_DATA_LOAD} from '#/plugin/analytics/tools/dashboard/path/store/actions'
import {selectors} from '#/plugin/analytics/tools/dashboard/path/store/selectors'

const reducer = combineReducers({
  tracking: makeReducer([], {
    [PATHS_DATA_LOAD]: (state, action) => action.tracking
  }),
  evaluations: makeListReducer(selectors.STORE_NAME + '.evaluations')
})

export {
  reducer
}
