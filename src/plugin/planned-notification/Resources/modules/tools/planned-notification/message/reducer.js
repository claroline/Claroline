import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {selectors} from '#/plugin/planned-notification/tools/planned-notification/store/selectors'

const reducer = combineReducers({
  picker: makeListReducer(selectors.STORE_NAME+'.messages.picker'),
  list: makeListReducer(selectors.STORE_NAME+'.messages.list', {
    sortBy: {property: 'id', direction: -1}
  }, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/'+selectors.STORE_NAME+'.messages.current']: () => true
    })
  }),
  current: makeFormReducer(selectors.STORE_NAME+'messages.current')
})

export {
  reducer
}