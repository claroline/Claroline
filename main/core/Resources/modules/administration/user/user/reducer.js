import {combineReducers, makeReducer} from '#/main/core/scaffolding/reducer'

import {makeListReducer} from '#/main/core/data/list/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'
import {COMPARE_OPEN, COMPARE_RESET} from '#/main/core/data/comparisonTable/actions'

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
  }),
  compare: combineReducers({
    selected: makeReducer([], {
      [COMPARE_OPEN]: (state, action) => action.data,
      [COMPARE_RESET]: () => []
    }),
    rolesUser0: makeListReducer('users.compare.rolesUser0'),
    rolesUser1: makeListReducer('users.compare.rolesUser1')
  })
})

export {
  reducer
}
