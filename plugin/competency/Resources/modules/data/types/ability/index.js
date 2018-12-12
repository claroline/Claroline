import {trans} from '#/main/app/intl/translation'

import {AbilityDisplay} from '#/plugin/competency/data/types/ability/components/display'
import {AbilityGroup} from '#/plugin/competency/data/types/ability/components/group'

const dataType = {
  name: 'ability',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-graduation-cap',
    label: trans('ability', {}, 'competency'),
    description: trans('ability_desc', {}, 'competency')
  },
  components: {
    details: AbilityDisplay,
    form: AbilityGroup
  }
}

export {
  dataType
}
