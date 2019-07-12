import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {selectors} from '#/main/core/administration/scheduled-task/store/selectors'

const reducer = combineReducers({
  isCronConfigured: makeReducer(false, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.isCronConfigured || false
  }),
  picker: makeListReducer(selectors.STORE_NAME + '.picker'),
  tasks: makeListReducer(selectors.STORE_NAME + '.tasks', {
    sortBy: {property: 'scheduledDate', direction: -1}
  }),
  task: makeFormReducer(selectors.STORE_NAME + '.task', {}, {
    users: makeListReducer(selectors.STORE_NAME + '.task.users')
  })
})

export {
  reducer
}
