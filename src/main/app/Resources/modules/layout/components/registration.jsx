import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {withRouter}from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'
import {PageFull} from '#/main/app/page/components/full'

import {RegistrationMain} from '#/main/app/security/registration/containers/main'

const HomeRegistrationComponent = (props) =>
  <div className="app-content" role="presentation">
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
              props.history.goBack()
            } else {
              props.history.push('/desktop')
            }
          } else {
            props.history.push('/login')
          }
        }}
      />
    </PageFull>
  </div>

HomeRegistrationComponent.propTypes = {
  history: T.shape({
    goBack: T.func.isRequired,
    push: T.func.isRequired
  }).isRequired
}

const HomeRegistration = withRouter(HomeRegistrationComponent)

export {
  HomeRegistration
}
