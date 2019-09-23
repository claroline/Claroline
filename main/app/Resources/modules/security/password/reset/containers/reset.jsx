import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'

import {ResetPasswordForm as ResetPasswordFormComponent} from '#/main/app/security/password/reset/components/reset'
import {reducer, selectors, actions} from '#/main/app/security/password/reset/store'
import {selectors as formSelectors} from '#/main/app/content/form/store'
import {withRouter} from '#/main/app/router'

const ResetPasswordForm = withRouter(withReducer(selectors.FORM_NAME, reducer)(
  connect(
    (state) => ({
      form: formSelectors.form(state, selectors.FORM_NAME)
    }),
    (dispatch) => ({
      reset(formData, callback) {
        dispatch(actions.reset(formData, callback))
      }
    })
  )(ResetPasswordFormComponent)
))

export {
  ResetPasswordForm
}
