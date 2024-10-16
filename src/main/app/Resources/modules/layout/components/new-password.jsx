import React from 'react'

import {PageSimple} from '#/main/app/page/components/simple'

import {ResetPasswordForm} from '#/main/app/security/password/reset/containers/reset'

const NewPassword = () =>
  <div className="app-content" role="presentation">
    <PageSimple
      className="page auth-page login-page main"
    >
      <ResetPasswordForm/>
    </PageSimple>
  </div>

export {
  NewPassword
}
