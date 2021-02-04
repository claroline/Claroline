import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/authentication/integration/ips/store/selectors'

const reducer = combineReducers({
  list: makeListReducer(selectors.LIST_NAME)
})

export {
  reducer
}
