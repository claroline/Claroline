import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/log/account/logs/store/selectors'

const reducer = combineReducers({
  functional: makeListReducer(selectors.FUNCTIONAL_LIST_NAME, {
    sortBy: {property: 'date', direction: -1}
  }),
  security: makeListReducer(selectors.SECURITY_LIST_NAME, {
    sortBy: {property: 'date', direction: -1}
  })
})

export {
  reducer
}
