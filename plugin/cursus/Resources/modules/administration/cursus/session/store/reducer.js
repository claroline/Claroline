import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {LIST_DATA_DELETE} from '#/main/app/content/list/store/actions'

import {selectors} from '#/plugin/cursus/administration/cursus/store/selectors'

const reducer = combineReducers({
  list: makeListReducer(selectors.STORE_NAME + '.sessions.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.sessions.current']: () => true,
      [LIST_DATA_DELETE + '/' + selectors.STORE_NAME + '.courses.current.sessions']: () => true
    })
  }),
  current: makeFormReducer(selectors.STORE_NAME + '.sessions.current', {}, {
    learners: makeListReducer(selectors.STORE_NAME + '.sessions.current.learners'),
    teachers: makeListReducer(selectors.STORE_NAME + '.sessions.current.teachers'),
    groups: makeListReducer(selectors.STORE_NAME + '.sessions.current.groups'),
    events: makeListReducer(selectors.STORE_NAME + '.sessions.current.events', {}, {
      invalidated: makeReducer(false, {
        [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.events.current']: () => true
      })
    })
  }),
  picker: makeListReducer(selectors.STORE_NAME + '.sessions.picker')
})

export {
  reducer
}
