import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/cursus/administration/cursus/store/selectors'

const reducer = combineReducers({
  list: makeListReducer(selectors.STORE_NAME + '.queues.list')
})

export {
  reducer
}
