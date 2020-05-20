import React from 'react'

import {Button} from '#/main/app/action/components/button'
import {SSO_BUTTON} from '#/main/authentication/buttons/sso'

import {constants} from '#/plugin/saml/sso/saml/constants'

const SamlButton = props =>
  <Button
    {...props}
    type={SSO_BUTTON}
    icon={constants.SERVICE_ICON}
    service={constants.SERVICE_NAME}
    target={['lightsaml_sp.login', {idp: props.idp}]}
  />

export {
  SamlButton
}
