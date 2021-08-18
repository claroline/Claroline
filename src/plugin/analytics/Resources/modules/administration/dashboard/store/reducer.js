
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors} from '#/plugin/analytics/administration/dashboard/store/selectors'

const reducer = combineReducers({
  count: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.count
  }),
  evaluations: makeListReducer(selectors.STORE_NAME+'.evaluations')
})

export {
  reducer
}
