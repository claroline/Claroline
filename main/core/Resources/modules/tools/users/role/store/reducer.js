import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/core/tools/users/store/selectors'

const reducer = combineReducers({
  picker: makeListReducer(selectors.STORE_NAME + '.roles.picker'),
  workspacePicker: makeListReducer(selectors.STORE_NAME + '.roles.workspacePicker'),
  list: makeListReducer(selectors.STORE_NAME + '.roles.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.roles.current']: () => true // todo : find better
    })
  }),
  current: makeFormReducer(selectors.STORE_NAME + '.roles.current', {}, {
    users: makeListReducer(selectors.STORE_NAME + '.roles.current.users'),
    groups: makeListReducer(selectors.STORE_NAME + '.roles.current.groups')
  })
})

export {
  reducer
}
