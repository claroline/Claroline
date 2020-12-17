import {trans} from '#/main/app/intl/translation'

import {constants} from '#/main/authentication/sso/github/constants'
import {GitHubButton} from '#/main/authentication/sso/github/components/button'
import {GitHubParameters} from '#/main/authentication/sso/github/components/parameters'

export default {
  name: constants.SERVICE_NAME,
  icon: constants.SERVICE_ICON,
  alt: constants.SERVICE_ICON_ALT,
  label: trans('github', {}, 'oauth'),

  components: {
    button: GitHubButton,
    parameters: GitHubParameters
  }
}
