import React from 'react'
import {PropTypes as T} from 'prop-types'

import {withRouter}from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {PageFull} from '#/main/app/page/components/full'

import {RegistrationMain} from '#/main/app/security/registration/containers/main'

const HomeRegistrationComponent = (props) =>
  <PageFull
    title={trans('registration')}
  >
    <RegistrationMain
      path="/registration"
      onRegister={() => {
        props.history.push('/desktop')
      }}
    />
  </PageFull>

HomeRegistrationComponent.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired
}

const HomeRegistration = withRouter(HomeRegistrationComponent)

export {
  HomeRegistration
}
