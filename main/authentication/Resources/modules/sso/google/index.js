import {trans} from '#/main/app/intl/translation'

import {constants} from '#/main/authentication/sso/google/constants'
import {GoogleButton} from '#/main/authentication/sso/google/components/button'
import {GoogleParameters} from '#/main/authentication/sso/google/components/parameters'

export default {
  name: constants.SERVICE_NAME,
  icon: constants.SERVICE_ICON,
  alt: constants.SERVICE_ICON_ALT,
  label: trans('google', {}, 'oauth'),

  components: {
    button: GoogleButton,
    parameters: GoogleParameters
  }
}
