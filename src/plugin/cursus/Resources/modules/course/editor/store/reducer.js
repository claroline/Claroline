import {makeFormReducer} from '#/main/app/content/form/store'

import {selectors} from '#/plugin/cursus/course/editor/store/selectors'
import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list'

const reducer = combineReducers({
  form: makeFormReducer(selectors.FORM_NAME),
  canceledSessions: makeListReducer(selectors.STORE_NAME+'.canceledSessions', {
    sortBy: {property: 'startDate', direction: -1}
  })
})

export {
  reducer
}
