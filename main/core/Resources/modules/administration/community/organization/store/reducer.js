import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeInstanceAction} from '#/main/app/store/actions'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors as baseSelectors} from '#/main/core/administration/community/store'

const reducer = combineReducers({
  picker: makeListReducer(baseSelectors.STORE_NAME+'.organizations.picker', {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, 'community')]: () => true
    })
  }),
  list: makeListReducer(baseSelectors.STORE_NAME+'.organizations.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/'+baseSelectors.STORE_NAME+'.organizations.current']: () => true, // todo : find better
      [makeInstanceAction(TOOL_LOAD, 'community')]: () => true
    })
  }),
  current: makeFormReducer(baseSelectors.STORE_NAME+'.organizations.current', {}, {
    workspaces: makeListReducer(baseSelectors.STORE_NAME+'.organizations.current.workspaces', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, 'community')]: () => true
      })
    }),
    users: makeListReducer(baseSelectors.STORE_NAME+'.organizations.current.users', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, 'community')]: () => true
      })
    }),
    groups: makeListReducer(baseSelectors.STORE_NAME+'.organizations.current.groups', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, 'community')]: () => true
      })
    }),
    managers: makeListReducer(baseSelectors.STORE_NAME+'.organizations.current.managers', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, 'community')]: () => true
      })
    })
  })
})

export {
  reducer
}
