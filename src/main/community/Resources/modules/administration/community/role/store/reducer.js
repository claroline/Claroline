import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {selectors as baseSelectors} from '#/main/community/administration/community/store/selectors'
import {constants} from '#/main/community/constants'

const reducer = combineReducers({
  list: makeListReducer(baseSelectors.STORE_NAME+'.roles.list', {
    filters: [{property: 'type', value: constants.ROLE_PLATFORM}]
  }, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/'+baseSelectors.STORE_NAME+'.roles.current']: () => true,
      [makeInstanceAction(TOOL_LOAD, 'community')]: () => true
    })
  }),
  current: makeFormReducer(baseSelectors.STORE_NAME+'.roles.current', {}, {
    users: makeListReducer(baseSelectors.STORE_NAME+'.roles.current.users', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, 'community')]: () => true
      })
    }),
    groups: makeListReducer(baseSelectors.STORE_NAME+'.roles.current.groups', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, 'community')]: () => true
      })
    })
  })
})

export {
  reducer
}
