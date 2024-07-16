import {createSelector} from 'reselect'

import {makeListReducer} from '#/main/app/content/list/store'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {LOAD_COURSE} from '#/plugin/cursus/course/store/actions'

const STORE_NAME = 'catalog'
const LIST_NAME = STORE_NAME+'.courses'

const store = (state) => state[STORE_NAME] || {}

const course = createSelector(
  [store],
  (store) => store.course
)

const selectors = {
  STORE_NAME,
  LIST_NAME,

  course
}

const reducer = combineReducers({
  courses: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'name', direction: 1},
    filters: [{property: 'public', value: true, locked: true}]
  }),
  course: makeReducer(null, {
    [LOAD_COURSE]: (state, action) => action.course
  })
})


export {
  reducer,
  selectors
}
