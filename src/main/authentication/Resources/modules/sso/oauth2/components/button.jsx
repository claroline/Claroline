import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {SsoButton} from '#/main/authentication/buttons/sso'

const OAuth2Button = (props) =>
  <SsoButton
    icon={props.icon}
    label={props.label}
    target={['apiv2_authentication_oauth2', {clientId: props.clientId, redirectPath: window.location.hash}]}
    confirm={props.confirm ? {
      message: props.confirm,
      button: trans('login')
    } : undefined}
    primary={props.primary}
  />

export {
  OAuth2Button
}
