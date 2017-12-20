import {combineReducers, makeReducer} from '#/main/core/utilities/redux'

import {makeListReducer} from '#/main/core/data/list/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'

import {FORM_RESET} from '#/main/core/data/form/actions'

import {PLATFORM_ROLE} from '#/main/core/user/role/constants'

const reducer = combineReducers({
  picker: makeListReducer('users.picker'),
  list: makeListReducer('users.list'),
  current: makeFormReducer('users.current', {}, {
    workspaces: makeListReducer('users.current.workspaces', {}, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/users.current']: () => true // todo : find better
      })
    }),
    groups: makeListReducer('users.current.groups', {}, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/users.current']: () => true // todo : find better
      })
    }),
    organizations: makeListReducer('users.current.organizations', {}, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/user.current']: () => true // todo : find better
      })
    }),
    roles: makeListReducer('users.current.roles', {
      filters: [{property: 'type', value: PLATFORM_ROLE}]
    }, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/users.current']: () => true // todo : find better
      })
    })
  })
})

export {
  reducer
}
