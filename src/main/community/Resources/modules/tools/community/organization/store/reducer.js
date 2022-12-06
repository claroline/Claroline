import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'

import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors} from '#/main/community/tools/community/organization/store/selectors'

const reducer = combineReducers({
  list: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'name', direction: 1}
  }, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, 'community')]: () => true,
      [makeInstanceAction(FORM_SUBMIT_SUCCESS, selectors.FORM_NAME)]: () => true
    })
  }),
  current: makeFormReducer(selectors.FORM_NAME, {}, {
    workspaces: makeListReducer(selectors.FORM_NAME+'.workspaces', {
      sortBy: {property: 'createdAt', direction: -1},
      filters: [
        {property: 'model', value: 0},
        {property: 'personal', value: 0}
      ]
    }, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, 'community')]: () => true
      })
    }),
    users: makeListReducer(selectors.FORM_NAME+'.users', {
      sortBy: {property: 'lastName', direction: 1}
    }, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, 'community')]: () => true
      })
    }),
    groups: makeListReducer(selectors.FORM_NAME+'.groups', {
      sortBy: {property: 'name', direction: 1}
    }, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, 'community')]: () => true
      })
    }),
    managers: makeListReducer(selectors.FORM_NAME+'.managers', {
      sortBy: {property: 'lastName', direction: 1}
    }, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, 'community')]: () => true
      })
    })
  })
})

export {
  reducer
}
