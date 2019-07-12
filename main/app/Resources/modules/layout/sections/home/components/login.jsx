/* global window */

import React from 'react'
import {PropTypes as T} from 'prop-types'

import {withRouter} from '#/main/app/router'
import {PageSimple} from '#/main/app/page/components/simple'

import {constants} from '#/main/app/security/login/constants'
import {LoginForm} from '#/main/app/security/login/containers/form'

const LoginPage = (props) =>
  <PageSimple
    className="login-page"
  >
    <LoginForm
      onLogin={(response) => {
        if (response.redirect) {
          switch (response.redirect.type) {
            case constants.LOGIN_REDIRECT_LAST:
              props.history.goBack()
              break
            case constants.LOGIN_REDIRECT_DESKTOP:
              props.history.push('/desktop')
              break
            case constants.LOGIN_REDIRECT_WORKSPACE:
              props.history.push('/workspaces/'+response.redirect.data.id)
              break
            case constants.LOGIN_REDIRECT_URL:
              window.location = response.redirect.data
              break
          }
        }
      }}
    />
  </PageSimple>

LoginPage.propTypes = {
  history: T.shape({
    push: T.func.isRequired,
    goBack: T.func.isRequired
  }).isRequired
}

const HomeLogin = withRouter(LoginPage)

export {
  HomeLogin
}
