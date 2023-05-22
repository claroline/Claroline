import {combineReducers} from '#/main/app/store/reducer'

import {reducer as crudReducer} from '#/main/example//tools/example/crud/store/reducer'

const reducer = combineReducers({
  crud: crudReducer
})

export {
  reducer
}
