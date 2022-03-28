import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as securityActions, selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as configSelectors} from '#/main/app/config/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {LoginMain as LoginMainComponent} from '#/main/app/security/login/components/main'
import {reducer, selectors} from '#/main/app/security/login/store'

const LoginMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      platformName: configSelectors.param(state, 'name'),
      help: configSelectors.param(state, 'authentication.help'),
      registration: configSelectors.param(state, 'selfRegistration'),
      username: configSelectors.param(state, 'community.username'),
      resetPassword: configSelectors.param(state, 'authentication.changePassword'),
      internalAccount: selectors.internalAccount(state),
      showClientIp: selectors.showClientIp(state),
      sso: selectors.sso(state),
      clientIp: securitySelectors.clientIp(state)
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
  )(LoginMainComponent)
)

export {
  LoginMain
}
