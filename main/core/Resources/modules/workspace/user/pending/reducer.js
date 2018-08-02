import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

const reducer = combineReducers({
  picker: makeListReducer('pending.picker'),
  list: makeListReducer('pending.list')
})

export {
  reducer
}
