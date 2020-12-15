import React from 'react'

import {Button} from '#/main/app/action/components/button'
import {SSO_BUTTON} from '#/main/authentication/buttons/sso'

import {constants} from '#/main/authentication/sso/office_365/constants'

const Office365Button = props =>
  <Button
    {...props}
    type={SSO_BUTTON}
    icon={constants.SERVICE_ICON}
    service={constants.SERVICE_NAME}
  />

export {
  Office365Button
}
