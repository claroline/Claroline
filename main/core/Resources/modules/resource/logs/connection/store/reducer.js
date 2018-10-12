import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

const reducer = combineReducers({
  list: makeListReducer('connections.list')
})

export {
  reducer
}
