import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as formSelectors} from '#/main/app/content/form/store'
import {actions as securityActions} from '#/main/app/security/store'

import {LoginForm as LoginFormComponent} from '#/main/app/security/login/components/form'
import {reducer, selectors} from '#/main/app/security/login/store'

const LoginForm = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      registration: selectors.registration(state),
      sso: selectors.sso(state),
      primarySso: selectors.primarySso(state)
    }),
    (dispatch) => ({
      login(callback) {
        dispatch((dispatch, getState) => {
          const formData = formSelectors.data(formSelectors.form(getState(), selectors.FORM_NAME))
          return dispatch(securityActions.login(formData.username, formData.password, formData.remember_me)).then(() => {
            if (callback) {
              callback()
            }
          })
        })
        /*dispatch(formActions.save(selectors.FORM_NAME, ['claro_login'])).then(() => {
          if (callback) {
            callback()
          }
        })*/
      }
    })
  )(LoginFormComponent)
)

export {
  LoginForm
}
