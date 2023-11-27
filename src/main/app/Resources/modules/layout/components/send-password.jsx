/* global window */

import React from 'react'

import {PageSimple} from '#/main/app/page/components/simple'

import {SendPasswordForm} from '#/main/app/security/password/send/containers/send'

const SendPassword = () =>
  <PageSimple
    className="page auth-page login-page main"
  >
    <SendPasswordForm/>
  </PageSimple>

export {
  SendPassword
}
