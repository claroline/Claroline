import {combineReducers, makeReducer} from '#/main/core/scaffolding/reducer'

import {makeListReducer} from '#/main/core/data/list/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'

import {FORM_RESET, FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'

import {PLATFORM_ROLE} from '#/main/core/user/role/constants'

const reducer = combineReducers({
  picker: makeListReducer('roles.picker', {
    filters: [{property: 'type', value: PLATFORM_ROLE}]
  }),
  list: makeListReducer('roles.list', {
    filters: [{property: 'type', value: PLATFORM_ROLE}]
  }, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/roles.current']: () => true // todo : find better
    })
  }),
  current: makeFormReducer('roles.current', {}, {
    users: makeListReducer('roles.current.users', {}, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/roles.current']: () => true // todo : find better
      })
    }),
    groups: makeListReducer('roles.current.groups', {}, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/roles.current']: () => true // todo : find better
      })
    })
  })
})

export {
  reducer
}
