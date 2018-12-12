import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {CompetencyCard} from '#/plugin/competency/administration/competency/data/components/competency-card'

const FrameworkList = {
  open: (row) => ({
    type: LINK_BUTTON,
    target: `/frameworks/${row.id}`,
    label: trans('open', {}, 'actions')
  }),
  definition: [
    {
      name: 'name',
      label: trans('name'),
      displayed: true,
      type: 'string',
      primary: true
    }, {
      name: 'description',
      label: trans('description'),
      displayed: true,
      type: 'html'
    }, {
      name: 'scale',
      alias: 'scale.name',
      label: trans('scale', {}, 'competency'),
      displayed: true,
      type: 'string',
      calculated: (rowData) => rowData.scale.name
    }
  ],
  card: CompetencyCard
}

export {
  FrameworkList
}
