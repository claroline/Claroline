import {trans} from '#/main/app/intl/translation'

import {constants} from '#/plugin/saml/sso/saml/constants'
import {SamlButton} from '#/plugin/saml/sso/saml/components/button'

export default {
  name: constants.SERVICE_NAME,
  icon: constants.SERVICE_ICON,
  alt: constants.SERVICE_ICON_ALT,
  label: trans('saml', {}, 'oauth'),

  components: {
    button: SamlButton
  }
}
