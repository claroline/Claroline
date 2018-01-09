import {t} from '#/main/core/translation'

import {enumRole, PLATFORM_ROLE} from '#/main/core/user/role/constants'
import {RoleCard} from '#/main/core/administration/user/role/components/role-card.jsx'

const RoleList = {
  filters: [
    {property: 'type', value: PLATFORM_ROLE}
  ],
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
      type: 'string', // todo should be a new data type translated
      label: t('name'),
      renderer: (rowData) => t(rowData.translationKey),
      displayed: true
    }, {
      name: 'meta.type',
      type: 'enum',
      label: t('type'),
      options: {
        choices: enumRole
      },
      alias: 'type',
      displayed: true
    },
    {
      name: 'workspace.name',
      type: 'string',
      label: t('workspace'),
      displayed: true,
      filterable: false
    }
  ],
  card: RoleCard
}

export {
  RoleList
}
