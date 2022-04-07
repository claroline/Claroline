import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {SsoButton} from '#/main/authentication/buttons/sso'

import {constants} from '#/plugin/saml/sso/saml/constants'

const SamlButton = props =>
  <SsoButton
    icon={constants.SERVICE_ICON}
    service={constants.SERVICE_NAME}
    label={props.label}
    target={['lightsaml_sp.login', {idp: props.idp, redirectPath: window.location.hash}]}
    confirm={props.confirm ? {
      message: props.confirm,
      button: trans('login')
    } : undefined}
  />

export {
  SamlButton
}
