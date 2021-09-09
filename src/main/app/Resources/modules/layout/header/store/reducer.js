import {combineReducers, makeReducer} from '#/main/app/store/reducer'

const reducer = combineReducers({
  menus: makeReducer({}),
  display: makeReducer({})
})

export {
  reducer
}
