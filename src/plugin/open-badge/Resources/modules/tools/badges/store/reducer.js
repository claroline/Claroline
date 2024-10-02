import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {TOOL_OPEN} from '#/main/core/tool/store/actions'

import {selectors} from '#/plugin/open-badge/tools/badges/store/selectors'
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
  mine: makeListReducer(selectors.STORE_NAME + '.mine', {
    sortBy: {property: 'issuedOn', direction: -1}
  }, {
    loaded: makeReducer(false, {
      [CONTEXT_OPEN]: () => false
    }),
    invalidated: makeReducer(false, {
      [TOOL_OPEN]: () => true
    })
  }),
  current: makeFormReducer(selectors.FORM_NAME, {}, {
    assertions: makeListReducer(selectors.FORM_NAME + '.assertions', {
      sortBy: {property: 'issuedOn', direction: -1}
    }, {
      invalidated: makeReducer(false, {
        [TOOL_OPEN]: () => true
      })
    })
  }),
  assertion: makeFormReducer(selectors.STORE_NAME + '.badges.assertion', {}, {
    evidences: makeListReducer(selectors.STORE_NAME + '.badges.assertion.evidences', {}, {
      invalidated: makeReducer(false, {
        [TOOL_OPEN]: () => true
      })
    })
  })
})

export {
  reducer
}
