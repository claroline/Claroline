import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'

const reducer = combineReducers({
  picker: makeListReducer('pending.picker'),
  list: makeListReducer('pending.list')
})

export {
  reducer
}
