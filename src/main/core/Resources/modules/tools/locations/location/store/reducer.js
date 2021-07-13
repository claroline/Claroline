import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {selectors as baseSelectors} from '#/main/core/tools/locations/store/selectors'

const reducer = combineReducers({
  list: makeListReducer(baseSelectors.STORE_NAME+'.locations.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/'+baseSelectors.STORE_NAME+'.locations.current']: () => true,
      [makeInstanceAction(TOOL_LOAD, 'locations')]: () => true
    })
  }),
  current: makeFormReducer(baseSelectors.STORE_NAME+'.locations.current', {}, {
    users: makeListReducer(baseSelectors.STORE_NAME+'.locations.current.users', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, 'locations')]: () => true
      })
    }),
    organizations: makeListReducer(baseSelectors.STORE_NAME+'.locations.current.organizations', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, 'locations')]: () => true
      })
    }),
    groups: makeListReducer(baseSelectors.STORE_NAME+'.locations.current.groups', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, 'locations')]: () => true
      })
    })
  })
})

export {
  reducer
}
