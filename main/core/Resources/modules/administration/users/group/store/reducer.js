import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {PLATFORM_ROLE} from '#/main/core/user/role/constants'
import {selectors as baseSelectors} from '#/main/core/administration/users/store'

const reducer = combineReducers({
  picker: makeListReducer(baseSelectors.STORE_NAME+'.groups.picker'),
  list: makeListReducer(baseSelectors.STORE_NAME+'.groups.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/'+baseSelectors.STORE_NAME+'.groups.current']: () => true // todo : find better
    })
  }),
  current: makeFormReducer(baseSelectors.STORE_NAME+'.groups.current', {}, {
    users: makeListReducer(baseSelectors.STORE_NAME+'.groups.current.users'),
    roles: makeListReducer(baseSelectors.STORE_NAME+'.groups.current.roles', {
      filters: [{property: 'type', value: PLATFORM_ROLE}]
    }),
    organizations: makeListReducer(baseSelectors.STORE_NAME+'.groups.current.organizations')
  })
})

export {
  reducer
}
