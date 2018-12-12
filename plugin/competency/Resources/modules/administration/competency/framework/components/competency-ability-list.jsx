import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {CompetencyAbilityCard} from '#/plugin/competency/administration/competency/data/components/competency-ability-card'

const CompetencyAbilityList = {
  open: (row) => ({
    type: LINK_BUTTON,
    target: `/frameworks/${row.competency.id}/ability/${row.id}`,
    label: trans('open', {}, 'actions')
  }),
  definition: [
    {
      name: 'ability.name',
      label: trans('name'),
      displayed: true,
      type: 'string',
      primary: true
    }, {
      name: 'level.name',
      label: trans('level', {}, 'competency'),
      displayed: true,
      type: 'string'
    }
  ],
  card: CompetencyAbilityCard
}

export {
  CompetencyAbilityList
}
