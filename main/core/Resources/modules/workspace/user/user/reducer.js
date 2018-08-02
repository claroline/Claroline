import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

const reducer = combineReducers({
  picker: makeListReducer('users.picker'),
  list: makeListReducer('users.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/users.current']: () => true // todo : find better
    })
  }),
  current: makeFormReducer('users.current', {}, {
    workspaces: makeListReducer('users.current.workspaces'),
    groups: makeListReducer('users.current.groups'),
    organizations: makeListReducer('users.current.organizations'),
    roles: makeListReducer('users.current.roles')
  })
})

export {
  reducer
}
