import {trans} from '#/main/app/intl/translation'

import {constants} from '#/main/authentication/sso/twitter/constants'
import {TwitterButton} from '#/main/authentication/sso/twitter/components/button'
import {TwitterParameters} from '#/main/authentication/sso/twitter/components/parameters'

export default {
  name: constants.SERVICE_NAME,
  icon: constants.SERVICE_ICON,
  alt: constants.SERVICE_ICON_ALT,
  label: trans('twitter', {}, 'oauth'),

  components: {
    button: TwitterButton,
    parameters: TwitterParameters
  }
}
