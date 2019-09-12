import {trans} from '#/main/app/intl/translation'

import {constants} from '#/main/authentication/sso/windows_live/constants'
import {WindowsLiveButton} from '#/main/authentication/sso/windows_live/components/button'
import {WindowsLiveParameters} from '#/main/authentication/sso/windows_live/components/parameters'

export default {
  name: constants.SERVICE_NAME,
  icon: constants.SERVICE_ICON,
  alt: constants.SERVICE_ICON_ALT,
  label: trans('windows_live', {}, 'oauth'),

  components: {
    button: WindowsLiveButton,
    parameters: WindowsLiveParameters
  }
}
