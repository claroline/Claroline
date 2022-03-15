import {trans} from '#/main/app/intl/translation'

import {BadgesMain} from '#/plugin/open-badge/account/badges/containers/main'

export default {
  name: 'badges',
  icon: 'fa fa-fw fa-trophy',
  label: trans('my_badges', {}, 'badge'),
  component: BadgesMain,
  styles: ['claroline-distribution-plugin-open-badge-badges-tool']
}
