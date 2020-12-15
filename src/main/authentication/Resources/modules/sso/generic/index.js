import {trans} from '#/main/app/intl/translation'

import {constants} from '#/main/authentication/sso/generic/constants'
import {GenericButton} from '#/main/authentication/sso/generic/components/button'
import {GenericParameters} from '#/main/authentication/sso/generic/components/parameters'

export default {
  name: constants.SERVICE_NAME,
  icon: constants.SERVICE_ICON,
  alt: constants.SERVICE_ICON_ALT,
  label: trans('generic', {}, 'oauth'),

  components: {
    button: GenericButton,
    parameters: GenericParameters
  }
}
