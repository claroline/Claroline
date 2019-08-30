import {combineReducers, makeReducer} from '#/main/app/store/reducer'

const reducer = combineReducers({
  show_locale: makeReducer(false),
  content: makeReducer(null)
})

export {
  reducer
}
