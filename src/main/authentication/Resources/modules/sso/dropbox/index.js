import {trans} from '#/main/app/intl/translation'

import {constants} from '#/main/authentication/sso/dropbox/constants'
import {DropboxButton} from '#/main/authentication/sso/dropbox/components/button'
import {DropboxParameters} from '#/main/authentication/sso/dropbox/components/parameters'

export default {
  name: constants.SERVICE_NAME,
  icon: constants.SERVICE_ICON,
  alt: constants.SERVICE_ICON_ALT,
  label: trans('dropbox', {}, 'oauth'),

  components: {
    button: DropboxButton,
    parameters: DropboxParameters
  }
}
