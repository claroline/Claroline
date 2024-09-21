import React from 'react'
import {useHistory, useParams} from 'react-router-dom'

import {PageSimple} from '#/main/app/page/components/simple'
import {LoginMain} from '#/main/app/security/login/containers/main'

const PlatformLogin = () => {
  const history = useHistory()
  const routeParams = useParams()

  return (
    <PageSimple
      className="auth-page login-page"
    >
      <LoginMain
        forceInternalAccount={routeParams.forceInternalAccount}
        onLogin={() => {
          history.push('/desktop')
        }}
      />
    </PageSimple>
  )
}

export {
  PlatformLogin
}
