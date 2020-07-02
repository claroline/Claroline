import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {selectors} from '#/plugin/cursus/administration/cursus/store/selectors'

const reducer = combineReducers({
  list: makeListReducer(selectors.STORE_NAME + '.courses.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.courses.current']: () => true
    })
  }),
  current: makeFormReducer(selectors.STORE_NAME + '.courses.current', {}, {
    users: makeListReducer(selectors.STORE_NAME + '.courses.current.users'),
    organizations: combineReducers({
      list: makeListReducer(selectors.STORE_NAME + '.courses.current.organizations.list'),
      picker: makeListReducer(selectors.STORE_NAME + '.courses.current.organizations.picker')
    }),
    sessions: makeListReducer(selectors.STORE_NAME + '.courses.current.sessions', {}, {
      invalidated: makeReducer(false, {
        [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.sessions.current']: () => true
      })
    })
  }),
  picker: makeListReducer(selectors.STORE_NAME + '.courses.picker')
})

export {
  reducer
}
