import {trans} from '#/main/app/intl/translation'

import {constants} from '#/main/authentication/sso/facebook/constants'
import {FacebookButton} from '#/main/authentication/sso/facebook/components/button'
import {FacebookParameters} from '#/main/authentication/sso/facebook/components/parameters'

export default {
  name: constants.SERVICE_NAME,
  icon: constants.SERVICE_ICON,
  alt: constants.SERVICE_ICON_ALT,
  label: trans('facebook', {}, 'oauth'),

  components: {
    button: FacebookButton,
    parameters: FacebookParameters
  }
}
