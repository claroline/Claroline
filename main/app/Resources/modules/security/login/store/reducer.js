import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/app/security/login/store/selectors'

export const reducer = combineReducers({
  /**
   * Does the self registration enabled (to know if we need to display the button) ?
   */
  registration: makeReducer(false),

  /**
   * The list of enabled sso for the platform.
   */
  sso: makeReducer([]),

  /**
   * The standard auth form.
   */
  form: makeFormReducer(selectors.FORM_NAME, {new: true})
})
