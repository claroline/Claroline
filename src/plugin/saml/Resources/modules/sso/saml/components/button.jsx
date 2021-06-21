import React from 'react'

import {url} from '#/main/app/api'
import {Button} from '#/main/app/action/components/button'
import {SSO_BUTTON} from '#/main/authentication/buttons/sso'

import {constants} from '#/plugin/saml/sso/saml/constants'

const SamlButton = props =>
  <Button
    {...props}
    type={SSO_BUTTON}
    icon={constants.SERVICE_ICON}
    service={constants.SERVICE_NAME}
    target={url(['lightsaml_sp.login', {idp: props.idp}], {redirectPath: window.location.hash})}
  />

export {
  SamlButton
}
