import React from 'react'
import {useHistory} from 'react-router-dom'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'
import {PageFull} from '#/main/app/page/components/full'

import {RegistrationMain} from '#/main/app/security/registration/containers/main'

const PlatformRegistration = () => {
  const history = useHistory()

  return (
    <PageFull
      title={trans('registration')}
    >
      <RegistrationMain
        className="content-sm"
        path="/registration"
        onRegister={(response) => {
          if (get(response, 'user')) {
            if (document.referrer && -1 !== document.referrer.indexOf(param('serverUrl'))) {
              // only redirect to previous url if it's part of the claroline platform
              history.goBack()
            } else {
              history.push('/desktop')
            }
          } else {
            history.push('/login')
          }
        }}
      />
    </PageFull>
  )
}

export {
  PlatformRegistration
}
