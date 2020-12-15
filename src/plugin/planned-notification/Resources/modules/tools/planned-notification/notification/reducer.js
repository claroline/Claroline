import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {selectors} from '#/plugin/planned-notification/tools/planned-notification/store/selectors'

const reducer = combineReducers({
  rolesPicker: makeListReducer(selectors.STORE_NAME+'.notifications.rolesPicker'),
  list: makeListReducer(selectors.STORE_NAME+'.notifications.list', {
    sortBy: {property: 'id', direction: -1}
  }, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/'+selectors.STORE_NAME+'.notifications.current']: () => true
    })
  }),
  current: makeFormReducer(selectors.STORE_NAME+'.notifications.current'),
  manual: makeFormReducer(selectors.STORE_NAME+'.notifications.manual')
})

export {
  reducer
}