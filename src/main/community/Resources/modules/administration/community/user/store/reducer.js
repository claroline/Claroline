import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {selectors as baseSelectors} from '#/main/community/administration/community/store/selectors'

const reducer = combineReducers({
  list: makeListReducer(baseSelectors.STORE_NAME+'.users.list', {
    sortBy: {property: 'created', direction: -1}
  }, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/'+baseSelectors.STORE_NAME+'.users.current']: () => true,
      [makeInstanceAction(TOOL_LOAD, 'community')]: () => true
    })
  }),
  current: makeFormReducer(baseSelectors.STORE_NAME+'.users.current', {}, {
    groups: makeListReducer(baseSelectors.STORE_NAME+'.users.current.groups', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, 'community')]: () => true
      })
    }),
    organizations: makeListReducer(baseSelectors.STORE_NAME+'.users.current.organizations', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, 'community')]: () => true
      })
    }),
    roles: makeListReducer(baseSelectors.STORE_NAME+'.users.current.roles', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, 'community')]: () => true
      })
    })
  }),
  limitReached: makeReducer(false, {
    [makeInstanceAction(TOOL_LOAD, 'community')]: (state, action) => action.toolData.usersLimitReached
  })
})

export {
  reducer
}
