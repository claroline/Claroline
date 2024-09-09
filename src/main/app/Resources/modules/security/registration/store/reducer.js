import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/app/security/registration/store/selectors'
import {REGISTRATION_DATA_LOAD} from '#/main/app/security/registration/store/actions'

export const reducer = combineReducers({
  termOfService: makeReducer(null, {
    [REGISTRATION_DATA_LOAD]: (state, action) => action.data.termOfService || null
  }),
  facets: makeReducer([], {
    [REGISTRATION_DATA_LOAD]: (state, action) => action.data.facets || []
  }),
  options: makeReducer({}, {
    [REGISTRATION_DATA_LOAD]: (state, action) => action.data.options
  }),
  form: makeFormReducer(selectors.FORM_NAME, {
    new: true
  })
})
