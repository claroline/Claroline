import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {constants} from '#/main/community/constants'
import {selectors as baseSelectors} from '#/main/community/tools/community/store/selectors'

import {ROLE_WORKSPACE_RIGHTS_LOAD, ROLE_DESKTOP_RIGHTS_LOAD, ROLE_ADMINISTRATION_RIGHTS_LOAD} from '#/main/community/tools/community/role/store/actions'
import {selectors} from '#/main/community/tools/community/role/store/selectors'

const reducer = combineReducers({
  list: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'name', direction: 1}
  }, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: () => true,
      [makeInstanceAction(FORM_SUBMIT_SUCCESS, selectors.FORM_NAME)]: () => true
    }),
    filters: makeReducer([], {
      [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: (state, action) => 'desktop' === action.context.type ? [
        {property: 'type', value: constants.ROLE_PLATFORM}
      ] : state
    })
  }),
  current: makeFormReducer(selectors.FORM_NAME, {}, {
    users: makeListReducer(selectors.FORM_NAME + '.users', {
      sortBy: {property: 'lastName', direction: 1}
    }, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: () => true
      })
    }),
    groups: makeListReducer(selectors.FORM_NAME + '.groups', {
      sortBy: {property: 'name', direction: 1}
    }, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: () => true
      })
    }),
    workspaceRights: makeReducer({}, {
      [ROLE_WORKSPACE_RIGHTS_LOAD]: (state, action) => action.rights
    }),
    desktopRights: makeReducer({}, {
      [ROLE_DESKTOP_RIGHTS_LOAD]: (state, action) => action.rights
    }),
    administrationRights: makeReducer({}, {
      [ROLE_ADMINISTRATION_RIGHTS_LOAD]: (state, action) => action.rights
    })
  })
})

export {
  reducer
}
