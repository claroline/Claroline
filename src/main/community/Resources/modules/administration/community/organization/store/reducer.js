import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'

import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors as baseSelectors} from '#/main/community/administration/community/store/selectors'

const reducer = combineReducers({
  list: makeListReducer(baseSelectors.STORE_NAME+'.organizations.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/'+baseSelectors.STORE_NAME+'.organizations.current']: () => true,
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
