import {makeListReducer} from '#/main/app/content/list/store'
import {combineReducers} from '#/main/app/store/reducer'
import {selectors} from '#/plugin/cursus/tools/trainings/editor/store/selectors'

const reducer = combineReducers({
  courses: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'name', direction: 1},
    filters: [{property: 'archived', value: true}]
  })
})

export {
  reducer
}
