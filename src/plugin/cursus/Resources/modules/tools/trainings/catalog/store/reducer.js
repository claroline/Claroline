import {makeListReducer} from '#/main/app/content/list/store'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store'

import {LOAD_COURSE} from '#/plugin/cursus/course/store/actions'
import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store/selectors'
import {selectors as courseSelectors} from '#/plugin/cursus/course/store/selectors'

const reducer = combineReducers({
  courses: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'name', direction: 1}
  }, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/'+courseSelectors.FORM_NAME]: () => true
    })
  }),
  course: makeReducer(null, {
    [LOAD_COURSE]: (state, action) => action.course
  })
})

export {
  reducer
}
