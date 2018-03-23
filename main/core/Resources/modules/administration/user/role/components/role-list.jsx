import React from 'react'

import {trans} from '#/main/core/translation'

import {enumRole} from '#/main/core/user/role/constants'
import {RoleCard} from '#/main/core/administration/user/role/components/role-card.jsx'

import {generateUrl} from '#/main/core/api/router'

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
      name: 'meta.type',
      alias: 'type',
      type: 'enum',
      label: trans('type'),
      options: {
        choices: enumRole
      },
      displayed: true
    }, {
      name: 'restrictions.maxUsers',
      type: 'number',
      label: trans('maxUsers'),
      displayed: false
    }, {
      name: 'workspace.name',
      type: 'string',
      label: trans('workspace'),
      displayed: true,
      filterable: false,
      renderer: (rowData) => {
        let WorkspaceLink

        if (rowData.workspace) {
          WorkspaceLink = <a href={generateUrl('claro_workspace_open', {workspaceId: rowData.workspace.id})}>{rowData.workspace.name}</a>
        } else {
          WorkspaceLink = '-'
        }

        return WorkspaceLink
      }
    }
  ],

  card: RoleCard
}

export {
  RoleList
}
