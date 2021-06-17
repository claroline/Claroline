
import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'

import {selectors} from '#/main/log/administration/logs/store/selectors'

const reducer = combineReducers({
  security: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'date', direction: -1}
  }),
  message: makeListReducer(selectors.MESSAGE_NAME, {
    sortBy: {property: 'date', direction: -1}
  }),
  functional: makeListReducer(selectors.FUNCTIONAL_NAME, {
    sortBy: {property: 'date', direction: -1}
  })
})

export {
  reducer
}
