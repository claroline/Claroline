import {combineReducers, makeReducer} from '#/main/app/store/reducer'

const reducer = combineReducers({
  menus: makeReducer([]),
  display: makeReducer({}),
  tools: makeReducer([]),
  notificationTools: makeReducer([]),
  administration: makeReducer([]),
  notifications: makeReducer({})
})

export {
  reducer
}
