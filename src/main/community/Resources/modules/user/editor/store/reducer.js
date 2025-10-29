import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeFetchReducer, constants} from '#/main/app/api/fetch'
import {makeFormReducer} from '#/main/app/content/form/store'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeInstanceAction} from '#/main/app/store/actions'
import {FORM_RESET} from '#/main/app/content/form/store/actions'

import {selectors} from '#/main/community/user/editor/store/selectors'

const reducer = combineReducers({
  form: makeFormReducer(selectors.FORM_NAME, {data: null}),
  organizations: makeFetchReducer(selectors.STORE_NAME + '.organizations', {}, {
    status: makeReducer(constants.STATUS_IDLE, {
      [makeInstanceAction(FORM_RESET, selectors.FORM_NAME)]: () => constants.STATUS_IDLE
    })
  }),
  roles: makeListReducer(selectors.STORE_NAME + '.roles', {
    sortBy: {property: 'name', direction: 1}
  }, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(FORM_RESET, selectors.FORM_NAME)]: () => true
    })
  })
})

export {
  reducer
}
