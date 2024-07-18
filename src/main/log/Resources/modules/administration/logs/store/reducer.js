
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'

import {selectors} from '#/main/log/administration/logs/store/selectors'
import {makeInstanceAction} from '#/main/app/store/actions'
import {TOOL_LOAD} from '#/main/core/tool/store'

const reducer = combineReducers({
  security: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'date', direction: -1}
  }),
  message: makeListReducer(selectors.MESSAGE_NAME, {
    sortBy: {property: 'date', direction: -1}
  }),
  functional: makeListReducer(selectors.FUNCTIONAL_NAME, {
    sortBy: {property: 'date', direction: -1}
  }),
  /*operational: makeListReducer(selectors.OPERATIONAL_NAME, {
    sortBy: {property: 'date', direction: -1}
  }),*/
  types: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.types
  })
})

export {
  reducer
}
