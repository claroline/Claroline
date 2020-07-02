import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {selectors} from '#/plugin/cursus/administration/cursus/store/selectors'

const reducer = combineReducers({
  list: makeListReducer(selectors.STORE_NAME + '.cursus.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.cursus.current']: () => true
    })
  }),
  current: makeFormReducer(selectors.STORE_NAME + '.cursus.current', {}, {
    users: makeListReducer(selectors.STORE_NAME + '.cursus.current.users'),
    groups: makeListReducer(selectors.STORE_NAME + '.cursus.current.groups'),
    organizations: combineReducers({
      list: makeListReducer(selectors.STORE_NAME + '.cursus.current.organizations.list'),
      picker: makeListReducer(selectors.STORE_NAME + '.cursus.current.organizations.picker')
    })
  })
})

export {
  reducer
}
