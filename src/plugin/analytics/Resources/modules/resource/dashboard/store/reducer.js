import {combineReducers, makeReducer} from '#/main/app/store/reducer'

const reducer = combineReducers({
  count: makeReducer(null)
})

export {
  reducer
}
