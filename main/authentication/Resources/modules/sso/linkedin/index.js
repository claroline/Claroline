import {trans} from '#/main/app/intl/translation'

import {constants} from '#/main/authentication/sso/linkedin/constants'
import {LinkedinButton} from '#/main/authentication/sso/linkedin/components/button'
import {LinkedinParameters} from '#/main/authentication/sso/linkedin/components/parameters'

export default {
  name: constants.SERVICE_NAME,
  icon: constants.SERVICE_ICON,
  alt: constants.SERVICE_ICON_ALT,
  label: trans('facebook', {}, 'oauth'),

  components: {
    button: LinkedinButton,
    parameters: LinkedinParameters
  }
}
