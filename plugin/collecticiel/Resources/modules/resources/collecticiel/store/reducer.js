import {combineReducers, makeReducer} from '#/main/app/store/reducer'

const reducer = combineReducers({
  dropzone: makeReducer({}, {})
})

export {
  reducer
}
