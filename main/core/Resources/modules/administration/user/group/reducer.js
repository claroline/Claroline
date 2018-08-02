import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {PLATFORM_ROLE} from '#/main/core/user/role/constants'

const reducer = combineReducers({
  picker: makeListReducer('groups.picker'),
  list: makeListReducer('groups.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/groups.current']: () => true // todo : find better
    })
  }),
  current: makeFormReducer('groups.current', {}, {
    users: makeListReducer('groups.current.users'),
    roles: makeListReducer('groups.current.roles', {
      filters: [{property: 'type', value: PLATFORM_ROLE}]
    }),
    organizations: makeListReducer('groups.current.organizations')
  })
})

export {
  reducer
}
