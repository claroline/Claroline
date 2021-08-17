import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'
import {makeFormReducer, FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store'

import {selectors} from '#/main/core/tools/locations/room/store/selectors'

const reducer = combineReducers({
  list: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'name', direction: 1}
  }, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/'+selectors.FORM_NAME]: () => true
    })
  }),
  // TODO : a form reducer is no longer required here
  current: makeFormReducer(selectors.FORM_NAME, {}, {
    events: makeListReducer(selectors.FORM_NAME+'.events', {
      sortBy: {property: 'startDate', direction: -1}
    })
  })
})

export {
  reducer
}