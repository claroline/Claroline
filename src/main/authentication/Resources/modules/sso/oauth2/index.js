import {trans} from '#/main/app/intl/translation'

import {constants} from '#/main/authentication/sso/oauth2/constants'
import {OAuth2Button} from '#/main/authentication/sso/oauth2/components/button'

export default {
  name: constants.SERVICE_NAME,
  icon: constants.SERVICE_ICON,
  label: trans('oauth2', {}, 'security'),

  components: {
    button: OAuth2Button
  }
}
