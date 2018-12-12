import {trans} from '#/main/app/intl/translation'

import {AbilityCard} from '#/plugin/competency/administration/competency/data/components/ability-card'

const AbilityList = {
  definition: [
    {
      name: 'name',
      label: trans('name'),
      displayed: true,
      type: 'string',
      primary: true
    }
  ],
  card: AbilityCard
}

export {
  AbilityList
}
