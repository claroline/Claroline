import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors} from '#/main/scheduler/administration/scheduled-task/store/selectors'

const reducer = combineReducers({
  tasks: makeListReducer(selectors.STORE_NAME + '.tasks', {
    sortBy: {property: 'scheduledDate', direction: -1}
  }, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  task: makeFormReducer(selectors.STORE_NAME + '.task', {}, {
    users: makeListReducer(selectors.STORE_NAME + '.task.users', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
      })
    })
  })
})

export {
  reducer
}
