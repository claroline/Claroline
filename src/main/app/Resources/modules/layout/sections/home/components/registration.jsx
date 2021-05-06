import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {withRouter}from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'
import {PageFull} from '#/main/app/page/components/full'

import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {constants} from '#/main/app/security/login/constants'
import {RegistrationMain} from '#/main/app/security/registration/containers/main'

const HomeRegistrationComponent = (props) =>
  <PageFull
    title={trans('registration')}
  >
    <RegistrationMain
      path="/registration"
      onRegister={(response) => {
        if (get(response, 'user') && get(response, 'redirect')) {
          switch (get(response, 'redirect.type')) {
            case constants.LOGIN_REDIRECT_LAST:
              if (document.referrer && -1 !== document.referrer.indexOf(param('serverUrl'))) {
                // only redirect to previous url if it's part of the claroline platform
                props.history.goBack()
              } else {
                props.history.push('/desktop')
              }

              break
            case constants.LOGIN_REDIRECT_WORKSPACE:
              props.history.push(workspaceRoute(response.redirect.data))
              break
            case constants.LOGIN_REDIRECT_URL:
              window.location = response.redirect.data
              break
            case constants.LOGIN_REDIRECT_DESKTOP:
            default:
              props.history.push('/desktop')
              break
          }
        } else {
          props.history.push('/login')
        }
      }}
    />
  </PageFull>

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
