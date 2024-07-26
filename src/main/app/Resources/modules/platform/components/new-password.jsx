import React from 'react'

import {PageSimple} from '#/main/app/page/components/simple'

import {ResetPasswordForm} from '#/main/app/security/password/reset/containers/reset'

const PlatformNewPassword = () =>
  <PageSimple
    className="auth-page login-page"
  >
    <ResetPasswordForm />
  </PageSimple>

export {
  PlatformNewPassword
}
