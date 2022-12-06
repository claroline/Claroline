import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors as baseSelectors} from '#/main/community/tools/community/store/selectors'
import {selectors} from '#/main/community/tools/community/group/store/selectors'

const reducer = combineReducers({
  list: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'name', direction: 1}
  }, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: () => true,
      [makeInstanceAction(FORM_SUBMIT_SUCCESS, selectors.FORM_NAME)]: () => true
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
    roles: makeListReducer(selectors.FORM_NAME + '.roles', {
      sortBy: {property: 'name', direction: 1}
    }, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: () => true
      })
    }),
    organizations: makeListReducer(selectors.FORM_NAME + '.organizations', {
      sortBy: {property: 'name', direction: 1}
    }, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: () => true
      })
    })
  })
})

export {
  reducer
}
