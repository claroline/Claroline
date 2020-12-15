import {trans} from '#/main/app/intl/translation'

import {AbilityDisplay} from '#/plugin/competency/data/types/ability/components/display'
import {AbilityInput} from '#/plugin/competency/data/types/ability/components/input'

const dataType = {
  name: 'ability',
  meta: {
    icon: 'fa fa-fw fa fa-atom',
    label: trans('ability', {}, 'data'),
    description: trans('ability_desc', {}, 'data')
  },
  components: {
    details: AbilityDisplay,
    form: AbilityInput
  }
}

export {
  dataType
}
