import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {selectors} from '#/main/core/tools/community/store/selectors'

const reducer = combineReducers({
  list: makeListReducer(selectors.STORE_NAME + '.users.list', {
    sortBy: {property: 'created', direction: -1}
  }, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.users.current']: () => true,
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  current: makeFormReducer(selectors.STORE_NAME + '.users.current', {}, {
    workspaces: makeListReducer(selectors.STORE_NAME + '.users.current.workspaces', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
      })
    }),
    groups: makeListReducer(selectors.STORE_NAME + '.users.current.groups', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
      })
    }),
    organizations: makeListReducer(selectors.STORE_NAME + '.users.current.organizations', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
      })
    }),
    roles: makeListReducer(selectors.STORE_NAME + '.users.current.roles', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
      })
    })
  }),
  limitReached: makeReducer(false, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.usersLimitReached
  })
})

export {
  reducer
}
