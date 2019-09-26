import {trans} from '#/main/app/intl/translation'

import {BadgeDisplay} from '#/plugin/open-badge/data/types/badge/components/display'
import {BadgeGroup} from '#/plugin/open-badge/data/types/badge/components/group'

const dataType = {
  name: 'badge',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-trophy',
    label: trans('badge'),
    description: trans('badges_desc')
  },
  components: {
    details: BadgeDisplay,
    form: BadgeGroup
  }
}

export {
  dataType
}
