import {makeInstanceAction} from '#/main/app/store/actions'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {TOOL_LOAD} from '#/main/core/tool/store'
import {selectors} from '#/plugin/cursus/tools/events/store/selectors'

const reducer = combineReducers({
  events: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'startDate', direction: -1},
    filters: [{property: 'status', value: 'not_ended'}]
  }),
  presences: makeListReducer(selectors.STORE_NAME+'.presences', {
    sortBy: {property: 'user', direction: 1}
  }),
  course: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => {
      return action.toolData.course
    }
  })
})

export {
  reducer
}
