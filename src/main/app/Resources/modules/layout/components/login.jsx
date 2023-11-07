import React from 'react'
import {PropTypes as T} from 'prop-types'

import {withRouter} from '#/main/app/router'
import {param} from '#/main/app/config'
import {PageSimple} from '#/main/app/page/components/simple'

import {LoginMain} from '#/main/app/security/login/containers/main'

const LoginPage = (props) =>
  <PageSimple
    className="authentication-page login-page"
  >
    <LoginMain
      forceInternalAccount={props.match.params.forceInternalAccount}
      onLogin={() => {
        if (document.referrer && -1 !== document.referrer.indexOf(param('serverUrl'))) {
          // only redirect to previous url if it's part of the claroline platform
          props.history.goBack()
        } else {
          props.history.push('/desktop')
        }
      }}
    />
  </PageSimple>

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
