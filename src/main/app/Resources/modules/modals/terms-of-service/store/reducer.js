import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {TERMS_OF_SERVICE_LOAD} from '#/main/app/modals/terms-of-service/store/actions'

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [TERMS_OF_SERVICE_LOAD]: () => true
  }),
  content: makeReducer(null, {
    [TERMS_OF_SERVICE_LOAD]: (state, action) => action.content
  })
})
