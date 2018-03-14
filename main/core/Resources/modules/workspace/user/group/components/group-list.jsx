
import {trans} from '#/main/core/translation'

import {GroupCard} from '#/main/core/administration/user/group/components/group-card.jsx'

function getRoles(user) {
  return user.roles.map(role => role.translationKey).join(',')
}

function getWorkspaceRoles(workspace) {
  const roles = {}

  workspace.roles.forEach(role => {
    roles[role.id] = role.translationKey
  })

  return roles
}

const getGroupList = (workspace) => {
  return {
    open: {
      action: (row) => `#/groups/form/${row.id}`
    },
    definition: [
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      },
      {
        name: 'roles',
        type: 'enum',
        alias: 'role',
        options: {
          choices: getWorkspaceRoles(workspace)
        },
        label: trans('roles'),
        displayed: true,
        filterable: true,
        renderer: (rowData) => getRoles(rowData)
      }
    ],
    card: GroupCard
  }
}

export {
  getGroupList
}
