import {t} from '#/main/core/translation'

import {enumRole} from '#/main/core/user/role/constants'
import {RoleCard} from '#/main/core/administration/user/role/components/role-card.jsx'

const RoleList = {
  open: {
    action: (row) => `#/roles/${row.id}`
  },
  definition: [
    {
      name: 'name',
      type: 'string',
      label: t('code'),
      displayed: false,
      primary: true
    }, {
      name: 'translationKey',
      type: 'translation',
      label: t('name'),
      displayed: true
    }, {
      name: 'meta.type',
      alias: 'type',
      type: 'enum',
      label: t('type'),
      options: {
        choices: enumRole
      },
      displayed: true
    }, {
      name: 'meta.users',
      type: 'number',
      label: t('count_users'),
      displayed: true
    },  {
      name: 'restrictions.maxUsers',
      type: 'number',
      label: t('max_users'),
      displayed: true
    }, {
      name: 'workspace.name',
      type: 'string',
      label: t('workspace'),
      filterable: false
    }
  ],

  card: RoleCard
}

export {
  RoleList
}
