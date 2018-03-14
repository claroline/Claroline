import {combineReducers, makeReducer} from '#/main/core/scaffolding/reducer'

import {makeListReducer} from '#/main/core/data/list/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'

import {FORM_RESET, FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'

import {PLATFORM_ROLE} from '#/main/core/user/role/constants'

const reducer = combineReducers({
  picker: makeListReducer('groups.picker'),
  list: makeListReducer('groups.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/groups.current']: () => true // todo : find better
    })
  }),
  current: makeFormReducer('groups.current', {}, {
    users: makeListReducer('groups.current.users', {}, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/groups.current']: () => true // todo : find better
      })
    }),
    roles: makeListReducer('groups.current.roles', {
      filters: [{property: 'type', value: PLATFORM_ROLE}]
    }, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/groups.current']: () => true // todo : find better
      })
    }),
    organizations: makeListReducer('groups.current.organizations', {}, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/groups.current']: () => true // todo : find better
      })
    })
  })
})

export {
  reducer
}
