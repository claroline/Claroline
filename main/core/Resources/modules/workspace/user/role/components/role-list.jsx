import {trans} from '#/main/core/translation'

import {RoleCard} from '#/main/core/administration/user/role/components/role-card'

const RoleList = {
  open: {
    action: (row) => `#/roles/form/${row.id}`
  },
  definition: [
    {
      name: 'name',
      type: 'string',
      label: trans('code'),
      displayed: false,
      primary: true
    }, {
      name: 'translationKey',
      type: 'translation',
      label: trans('name'),
      displayed: true
    }, {
      name: 'restrictions.maxUsers',
      type: 'number',
      label: trans('maxUsers'),
      displayed: false
    }
  ],

  card: RoleCard
}

export {
  RoleList
}
