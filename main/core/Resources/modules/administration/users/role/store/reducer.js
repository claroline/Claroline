import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {selectors as baseSelectors} from '#/main/core/administration/users/store'
import {PLATFORM_ROLE} from '#/main/core/user/role/constants'

const reducer = combineReducers({
  picker: makeListReducer(baseSelectors.STORE_NAME+'.roles.picker'),
  list: makeListReducer(baseSelectors.STORE_NAME+'.roles.list', {
    filters: [{property: 'type', value: PLATFORM_ROLE}]
  }, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/'+baseSelectors.STORE_NAME+'.roles.current']: () => true // todo : find better
    })
  }),
  current: makeFormReducer(baseSelectors.STORE_NAME+'.roles.current', {}, {
    users: makeListReducer(baseSelectors.STORE_NAME+'.roles.current.users'),
    groups: makeListReducer(baseSelectors.STORE_NAME+'.roles.current.groups')
  })
})

export {
  reducer
}
