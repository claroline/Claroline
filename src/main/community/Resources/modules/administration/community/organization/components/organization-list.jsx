import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {OrganizationCard} from '#/main/core/user/data/components/organization-card'

const OrganizationList = {
  open: (row) => ({
    type: LINK_BUTTON,
    target: `/organizations/form/${row.id}`,
    label: trans('edit', {}, 'actions')
  }),
  definition: [
    {
      name: 'name',
      type: 'string',
      label: trans('name'),
      displayed: true,
      primary: true
    }, {
      name: 'meta.default',
      type: 'boolean',
      label: trans('default')
    }, {
      name: 'meta.parent',
      type: 'organization',
      label: trans('parent')
    }, {
      name: 'email',
      type: 'email',
      label: trans('email')
    }, {
      name: 'code',
      type: 'string',
      label: trans('code')
    }, {
      name: 'parent',
      type: 'organization',
      label: trans('parent')
    }, {
      name: 'restrictions.public',
      alias: 'public',
      type: 'boolean',
      label: trans('public')
    }
  ],
  card: OrganizationCard
}

export {
  OrganizationList
}
