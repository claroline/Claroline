import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/core/tools/trash/store/selectors'

const reducer = combineReducers({
  resources: makeListReducer(selectors.STORE_NAME + '.resources', {})
})

export {
  reducer
}
