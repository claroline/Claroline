import {combineReducers} from '#/main/app/store/reducer'

import {selectors} from '#/main/core/resource/dashboard/store/selectors'
import {makeFetchReducer} from '#/main/app/api/fetch/store'

const reducer = combineReducers({
  metrics: makeFetchReducer(selectors.STORE_NAME + '.metrics'),
  statuses: makeFetchReducer(selectors.STORE_NAME + '.statuses'),
  completion: makeFetchReducer(selectors.STORE_NAME + '.completion'),
  scores: makeFetchReducer(selectors.STORE_NAME + '.scores'),
  activity: makeFetchReducer(selectors.STORE_NAME + '.activity')
})

export {
  reducer
}
