import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/app/security/login/store/selectors'

export const reducer = combineReducers({
  /**
   * The list of enabled sso for the platform.
   */
  sso: makeReducer([]),

  /**
   * The standard auth form.
   */
  form: makeFormReducer(selectors.FORM_NAME, {new: true})
})
