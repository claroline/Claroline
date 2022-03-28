import React from 'react'
import {PropTypes as T} from 'prop-types'

import {withRouter} from '#/main/app/router'
import {param} from '#/main/app/config'
import {PageSimple} from '#/main/app/page/components/simple'

import {constants} from '#/main/app/security/login/constants'
import {LoginMain} from '#/main/app/security/login/containers/main'

import {route as workspaceRoute} from '#/main/core/workspace/routing'

const LoginPage = (props) =>
  <PageSimple
    className="authentication-page login-page"
  >
    <LoginMain
      forceInternalAccount={props.match.params.forceInternalAccount}
      onLogin={(response) => {
        if (response.redirect) {
          switch (response.redirect.type) {
            case constants.LOGIN_REDIRECT_LAST:
              if (document.referrer && -1 !== document.referrer.indexOf(param('serverUrl'))) {
                // only redirect to previous url if it's part of the claroline platform
                props.history.goBack()
              } else {
                props.history.push('/desktop')
              }
              break
            case constants.LOGIN_REDIRECT_WORKSPACE:
              props.history.push(workspaceRoute(response.redirect.data))
              break
            case constants.LOGIN_REDIRECT_URL:
              window.location = response.redirect.data
              break
            case constants.LOGIN_REDIRECT_DESKTOP:
            default:
              props.history.push('/desktop')
              break
          }
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
