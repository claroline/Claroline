import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {selectors as baseSelectors} from '#/main/core/administration/community/store'
import {USER_COMPARE} from '#/main/core/administration/community/user/store/actions'

const reducer = combineReducers({
  picker: makeListReducer(baseSelectors.STORE_NAME+'.users.picker'),
  list: makeListReducer(baseSelectors.STORE_NAME+'.users.list', {
    sortBy: {property: 'created', direction: -1}
  }, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/'+baseSelectors.STORE_NAME+'.users.current']: () => true // todo : find better
    })
  }),
  current: makeFormReducer(baseSelectors.STORE_NAME+'.users.current', {}, {
    groups: makeListReducer(baseSelectors.STORE_NAME+'.users.current.groups'),
    organizations: makeListReducer(baseSelectors.STORE_NAME+'.users.current.organizations'),
    roles: makeListReducer(baseSelectors.STORE_NAME+'.users.current.roles')
  }),
  compare: combineReducers({
    selected: makeReducer([], {
      [USER_COMPARE]: (state, action) => action.data
    }),
    groupsUser0: makeListReducer(baseSelectors.STORE_NAME+'.users.compare.groupsUser0'),
    groupsUser1: makeListReducer(baseSelectors.STORE_NAME+'.users.compare.groupsUser1'),
    organizationsUser0: makeListReducer(baseSelectors.STORE_NAME+'.users.compare.organizationsUser0'),
    organizationsUser1: makeListReducer(baseSelectors.STORE_NAME+'.users.compare.organizationsUser1'),
    rolesUser0: makeListReducer(baseSelectors.STORE_NAME+'.users.compare.rolesUser0'),
    rolesUser1: makeListReducer(baseSelectors.STORE_NAME+'.users.compare.rolesUser1')
  })
})

export {
  reducer
}
