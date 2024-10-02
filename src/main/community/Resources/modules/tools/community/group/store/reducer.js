import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'

import {TOOL_OPEN} from '#/main/core/tool/store/actions'

import {selectors} from '#/main/community/tools/community/group/store/selectors'
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
    users: makeListReducer(selectors.FORM_NAME + '.users', {
      sortBy: {property: 'lastName', direction: 1}
    }, {
      invalidated: makeReducer(false, {
        [TOOL_OPEN]: () => true
      })
    }),
    roles: makeListReducer(selectors.FORM_NAME + '.roles', {
      sortBy: {property: 'name', direction: 1}
    }, {
      invalidated: makeReducer(false, {
        [TOOL_OPEN]: () => true
      })
    }),
    organizations: makeListReducer(selectors.FORM_NAME + '.organizations', {
      sortBy: {property: 'name', direction: 1}
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
