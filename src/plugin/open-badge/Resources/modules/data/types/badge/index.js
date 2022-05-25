import {trans} from '#/main/app/intl/translation'

import {BadgeDisplay} from '#/plugin/open-badge/data/types/badge/components/display'
import {BadgeFilter} from '#/plugin/open-badge/data/types/badge/components/filter'
import {BadgeInput} from '#/plugin/open-badge/data/types/badge/components/input'

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
    input: BadgeInput,
    search: BadgeFilter
  }
}

export {
  dataType
}
