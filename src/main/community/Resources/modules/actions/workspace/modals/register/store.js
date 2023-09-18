import {makeListReducer} from '#/main/app/content/list/store'
import {combineReducers} from '#/main/app/store/reducer'

const STORE_NAME = 'registerPicker'
const USERS_LIST = STORE_NAME + '.users'
const GROUPS_LIST = STORE_NAME + '.groups'

const selectors = {
  STORE_NAME,
  USERS_LIST,
  GROUPS_LIST
}

const reducer = combineReducers({
  users: makeListReducer(selectors.USERS_LIST, {
    sortBy: {property: 'lastName', direction: 1}
  }),
  groups: makeListReducer(selectors.GROUPS_LIST, {
    sortBy: {property: 'name', direction: 1}
  })
})

export {
  reducer,
  selectors
}
