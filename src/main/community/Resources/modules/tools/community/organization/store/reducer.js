import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'

import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {TOOL_OPEN} from '#/main/core/tool/store/actions'

import {selectors} from '#/main/community/tools/community/organization/store/selectors'
import {CONTEXT_OPEN} from '#/main/app/context/store/actions'

const reducer = combineReducers({
  list: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'name', direction: 1}
  }, {
    loaded: makeReducer(false, {
      [CONTEXT_OPEN]: () => false
    }),
    invalidated: makeReducer(false, {
      [TOOL_OPEN]: () => true,
      [makeInstanceAction(FORM_SUBMIT_SUCCESS, selectors.FORM_NAME)]: () => true
    })
  }),
  current: makeFormReducer(selectors.FORM_NAME, {}, {
    workspaces: makeListReducer(selectors.FORM_NAME+'.workspaces', {
      sortBy: {property: 'name', direction: 1},
      filters: [
        {property: 'model', value: 0},
        {property: 'personal', value: 0}
      ]
    }, {
      invalidated: makeReducer(false, {
        [TOOL_OPEN]: () => true
      })
    }),
    users: makeListReducer(selectors.FORM_NAME+'.users', {
      sortBy: {property: 'lastName', direction: 1}
    }, {
      invalidated: makeReducer(false, {
        [TOOL_OPEN]: () => true
      })
    }),
    groups: makeListReducer(selectors.FORM_NAME+'.groups', {
      sortBy: {property: 'name', direction: 1}
    }, {
      invalidated: makeReducer(false, {
        [TOOL_OPEN]: () => true
      })
    }),
    managers: makeListReducer(selectors.FORM_NAME+'.managers', {
      sortBy: {property: 'lastName', direction: 1}
    }, {
      invalidated: makeReducer(false, {
        [TOOL_OPEN]: () => true
      })
    })
  })
})

export {
  reducer
}
