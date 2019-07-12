import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PageFull} from '#/main/app/page/components/full'

import {RegistrationMain} from '#/main/app/security/registration/containers/main'

const HomeRegistration = () =>
  <PageFull
    title={trans('registration')}
  >
    <RegistrationMain
      path="/registration"
    />
  </PageFull>

HomeRegistration.propTypes = {

}

export {
  HomeRegistration
}
