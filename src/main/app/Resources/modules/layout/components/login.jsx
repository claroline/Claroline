import React from 'react'
import {PropTypes as T} from 'prop-types'

import {withRouter} from '#/main/app/router'
import {PageSimple} from '#/main/app/page/components/simple'

import {LoginMain} from '#/main/app/security/login/containers/main'

const LoginPage = (props) =>
  <div className="app-content" role="presentation">
    <PageSimple
      className="auth-page login-page"
    >
      <LoginMain
        forceInternalAccount={props.match.params.forceInternalAccount}
        onLogin={() => props.history.push('/desktop')}
      />
    </PageSimple>
  </div>

LoginPage.propTypes = {
  match: T.shape({
    params: T.shape({
      forceInternalAccount: T.bool
    })
  }).isRequired,
  history: T.shape({
    push: T.func.isRequired,
    goBack: T.func.isRequired
  }).isRequired
}

const HomeLogin = withRouter(LoginPage)

export {
  HomeLogin
}
