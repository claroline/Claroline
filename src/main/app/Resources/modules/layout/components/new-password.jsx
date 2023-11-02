/* global window */

import React from 'react'

import {PageSimple} from '#/main/app/page/components/simple'

import {ResetPasswordForm} from '#/main/app/security/password/reset/containers/reset'

const NewPassword = () =>
  <PageSimple
    className="page authentication-page login-page main"
  >
    <ResetPasswordForm/>
  </PageSimple>

export {
  NewPassword
}
