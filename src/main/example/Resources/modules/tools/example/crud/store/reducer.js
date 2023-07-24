import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'

import {selectors} from '#/main/example/tools/example/crud/store/selectors'

const reducer = combineReducers({
  list: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'name', direction: 1}
  }, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(FORM_SUBMIT_SUCCESS, selectors.FORM_NAME)]: () => true
    })
  }),
  current: makeFormReducer(selectors.FORM_NAME)
})

export {
  reducer
}
