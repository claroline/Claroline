import {makeListReducer} from '#/main/app/content/list/store'
import {combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

const reducer = combineReducers({
  list: makeListReducer('tokens.list'),
  current: makeFormReducer('tokens.current', {}, {
  })
})

export {
  reducer
}
