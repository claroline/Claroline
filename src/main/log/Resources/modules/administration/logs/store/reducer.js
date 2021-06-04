
import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'

import {selectors} from '#/main/log/administration/logs/store/selectors'

const reducer = combineReducers({
  securityLogs: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'date', direction: -1}
  }),
  messageLogs: makeListReducer(selectors.MESSAGE_NAME, {
    sortBy: {property: 'date', direction: -1}
  }),
  functionalLogs: makeListReducer(selectors.FUNCTIONAL_NAME, {
    sortBy: {property: 'date', direction: -1}
  })
})

export {
  reducer
}
