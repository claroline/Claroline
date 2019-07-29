import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {PLATFORM_ROLE} from '#/main/core/user/role/constants'
import {selectors} from '#/main/core/tools/users/store/selectors'

const reducer = combineReducers({
  picker: makeListReducer(selectors.STORE_NAME + '.groups.picker'),
  list: makeListReducer(selectors.STORE_NAME + '.groups.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.groups.current']: () => true // todo : find better
    })
  }),
  current: makeFormReducer(selectors.STORE_NAME + '.groups.current', {}, {
    users: makeListReducer(selectors.STORE_NAME + '.groups.current.users'),
    roles: makeListReducer(selectors.STORE_NAME + '.groups.current.roles', {
      filters: [{property: 'type', value: PLATFORM_ROLE}]
    }),
    organizations: makeListReducer(selectors.STORE_NAME + '.groups.current.organizations')
  })
})

export {
  reducer
}
