import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {AssertionCard} from '#/plugin/open-badge/tools/badges/assertion/components/card'

const AssertionList = {
  open: (row) => ({
    label: trans('open'),
    type: LINK_BUTTON,
    target: `/badges/view/${row.badge.id}`
  }),
  definition: [
    {
      name: 'badge.name',
      label: trans('name'),
      displayed: true,
      primary: true
    },
    {
      name: 'badge.meta.enabled',
      label: trans('enabled', {}, 'openbadge'),
      displayed: true
    }
  ],
  card: AssertionCard
}

export {
  AssertionList
}
