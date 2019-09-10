import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {constants} from '#/main/core/user/constants'
import {selectors} from '#/main/core/tools/community/store/selectors'

const reducer = combineReducers({
  picker: makeListReducer(selectors.STORE_NAME + '.groups.picker', {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  list: makeListReducer(selectors.STORE_NAME + '.groups.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.groups.current']: () => true, // todo : find better
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  current: makeFormReducer(selectors.STORE_NAME + '.groups.current', {}, {
    users: makeListReducer(selectors.STORE_NAME + '.groups.current.users', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
      })
    }),
    roles: makeListReducer(selectors.STORE_NAME + '.groups.current.roles', {
      filters: [{property: 'type', value: constants.ROLE_PLATFORM}]
    }, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
      })
    }),
    organizations: makeListReducer(selectors.STORE_NAME + '.groups.current.organizations', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
      })
    })
  })
})

export {
  reducer
}
