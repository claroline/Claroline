import {makeReducer, combineReducers} from '#/main/app/store/reducer'

export const reducer = combineReducers({
  history: makeReducer([])
})
