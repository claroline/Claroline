import {combineReducers, makeReducer} from '#/main/app/store/reducer'

const reducer = combineReducers({
  display: makeReducer({}),
  content: makeReducer(null)
})

export {
  reducer
}
