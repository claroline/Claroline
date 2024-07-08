import {makeListReducer} from '#/main/app/content/list/store'
import {combineReducers} from '#/main/app/store/reducer'


const STORE_NAME = 'catalog'
const LIST_NAME = STORE_NAME+'.courses'
const store = (state) => state[STORE_NAME] || {}

const selectors = {
  STORE_NAME,
  LIST_NAME,
  store
}

const reducer = combineReducers({
  courses: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'name', direction: 1},
    filters: [{property: 'publicRegistration', value: true, locked: true}]
  })
})


export {
  reducer,
  selectors
}
