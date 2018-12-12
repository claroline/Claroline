import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {ScaleCard} from '#/plugin/competency/administration/competency/data/components/scale-card'

const ScaleList = {
  open: (row) => ({
    type: LINK_BUTTON,
    target: `/scales/form/${row.id}`,
    label: trans('edit', {}, 'actions')
  }),
  definition: [
    {
      name: 'name',
      label: trans('name'),
      displayed: true,
      type: 'string',
      primary: true
    }
  ],
  card: ScaleCard
}

export {
  ScaleList
}
