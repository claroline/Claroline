import React from 'react'

import {PageSimple} from '#/main/app/page/components/simple'

import {SendPasswordForm} from '#/main/app/security/password/send/containers/send'

const SendPassword = () =>
  <div className="app-content" role="presentation">
    <PageSimple
      className="page auth-page login-page main"
    >
      <SendPasswordForm/>
    </PageSimple>
  </div>

export {
  SendPassword
}
