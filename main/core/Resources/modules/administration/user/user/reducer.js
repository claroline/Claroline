import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {USER_COMPARE} from '#/main/core/administration/user/user/actions'

const reducer = combineReducers({
  picker: makeListReducer('users.picker'),
  list: makeListReducer('users.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/users.current']: () => true // todo : find better
    })
  }),
  current: makeFormReducer('users.current', {}, {
    groups: makeListReducer('users.current.groups'),
    organizations: makeListReducer('users.current.organizations'),
    roles: makeListReducer('users.current.roles')
  }),
  compare: combineReducers({
    selected: makeReducer([], {
      [USER_COMPARE]: (state, action) => action.data
    }),
    groupsUser0: makeListReducer('users.compare.groupsUser0'),
    groupsUser1: makeListReducer('users.compare.groupsUser1'),
    organizationsUser0: makeListReducer('users.compare.organizationsUser0'),
    organizationsUser1: makeListReducer('users.compare.organizationsUser1'),
    rolesUser0: makeListReducer('users.compare.rolesUser0'),
    rolesUser1: makeListReducer('users.compare.rolesUser1')
  })
})

export {
  reducer
}
