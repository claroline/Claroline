import {trans} from '#/main/app/intl/translation'

import {BadgeCard} from '#/plugin/open-badge/tools/badges/badge/components/card'

const BadgeList = {
  definition: [
    {
      name: 'name',
      label: trans('name'),
      displayed: true,
      primary: true
    },
    {
      name: 'meta.enabled',
      label: trans('enabled'),
      type: 'boolean',
      displayed: true
    },
    {
      name: 'assignable',
      label: trans('assignable', {}, 'openbadge'),
      type: 'boolean',
      displayed: true,
      filterable: true
    },
    {
      name: 'workspace',
      label: trans('workspace'),
      type: 'workspace',
      displayed: true,
      filterable: true
    }
  ],
  card: BadgeCard
}

export {
  BadgeList
}
