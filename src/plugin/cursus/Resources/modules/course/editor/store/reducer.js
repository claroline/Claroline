import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list'
import {constants, makeFetchReducer} from '#/main/app/api/fetch'
import {makeFormReducer} from '#/main/app/content/form/store'
import {makeInstanceAction} from '#/main/app/store/actions'
import {FORM_RESET} from '#/main/app/content/form/store/actions'

import {selectors} from '#/plugin/cursus/course/editor/store/selectors'

const reducer = combineReducers({
  form: makeFormReducer(selectors.FORM_NAME),
  organizations: makeFetchReducer(selectors.STORE_NAME + '.organizations', {}, {
    status: makeReducer(constants.STATUS_IDLE, {
      [makeInstanceAction(FORM_RESET, selectors.FORM_NAME)]: () => constants.STATUS_IDLE
    })
  }),
  canceledSessions: makeListReducer(selectors.STORE_NAME+'.canceledSessions', {
    sortBy: {property: 'startDate', direction: -1}
  })
})

export {
  reducer
}
