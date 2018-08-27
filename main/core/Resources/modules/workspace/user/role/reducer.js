import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

const reducer = combineReducers({
  picker: makeListReducer('roles.picker', {}),
  workspacePicker: makeListReducer('roles.workspacePicker'),
  list: makeListReducer('roles.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/roles.current']: () => true // todo : find better
    })
  }),
  current: makeFormReducer('roles.current', {}, {
    users: makeListReducer('roles.current.users'),
    groups: makeListReducer('roles.current.groups')
  })
})

export {
  reducer
}
