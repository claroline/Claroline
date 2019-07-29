import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/core/tools/users/store/selectors'

const reducer = combineReducers({
  picker: makeListReducer(selectors.STORE_NAME + '.pending.picker'),
  list: makeListReducer(selectors.STORE_NAME + '.pending.list')
})

export {
  reducer
}
