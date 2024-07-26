/* global window */

import React from 'react'

import {PageSimple} from '#/main/app/page/components/simple'

import {SendPasswordForm} from '#/main/app/security/password/send/containers/send'

const PlatformSendPassword = () =>
  <PageSimple
    className="auth-page login-page"
  >
    <SendPasswordForm/>
  </PageSimple>

export {
  PlatformSendPassword
}
