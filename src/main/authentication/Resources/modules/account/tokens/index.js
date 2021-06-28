import {trans} from '#/main/app/intl/translation'

import {TokensMain} from '#/main/authentication/account/tokens/containers/main'

export default {
  name: 'tokens',
  icon: 'fa fa-fw fa-coins',
  label: trans('tokens', {}, 'security'),
  component: TokensMain
}
