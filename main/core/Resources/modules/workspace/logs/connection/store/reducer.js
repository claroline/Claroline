import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

const reducer = combineReducers({
  list: makeListReducer('connections.list', {
    sortBy: {property: 'connectionDate', direction: -1}
  })
})

export {
  reducer
}
