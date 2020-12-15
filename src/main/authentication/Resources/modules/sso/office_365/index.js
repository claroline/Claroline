import {trans} from '#/main/app/intl/translation'

import {constants} from '#/main/authentication/sso/office_365/constants'
import {Office365Button} from '#/main/authentication/sso/office_365/components/button'
import {Office365Parameters} from '#/main/authentication/sso/office_365/components/parameters'

export default {
  name: constants.SERVICE_NAME,
  icon: constants.SERVICE_ICON,
  alt: constants.SERVICE_ICON_ALT,
  label: trans('office_365', {}, 'oauth'),

  components: {
    button: Office365Button,
    parameters: Office365Parameters
  }
}
