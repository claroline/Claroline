import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as securityActions} from '#/main/app/security/store'
import {selectors as configSelectors} from '#/main/app/config/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {LoginForm as LoginFormComponent} from '#/main/app/security/login/components/form'
import {reducer, selectors} from '#/main/app/security/login/store'

const LoginForm = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      platformName: configSelectors.param(state, 'name'),
      help: configSelectors.param(state, 'authentication.help'),
      registration: configSelectors.param(state, 'selfRegistration'),
      resetPassword: configSelectors.param(state, 'authentication.changePassword'),
      sso: selectors.sso(state)
    }),
    (dispatch) => ({
      login(callback) {
        return dispatch((dispatch, getState) => {
          const formData = formSelectors.data(formSelectors.form(getState(), selectors.FORM_NAME))

          dispatch(formActions.submit(selectors.FORM_NAME))

          return dispatch(securityActions.login(formData.username, formData.password, formData.remember_me)).then(
            (response) => {
              if (callback) {
                callback(response)
              }
            },
            (response) => dispatch(formActions.setErrors(selectors.FORM_NAME, {password: trans(response, {}, 'security')}))
          )
        })
      }
    })
  )(LoginFormComponent)
)

export {
  LoginForm
}
