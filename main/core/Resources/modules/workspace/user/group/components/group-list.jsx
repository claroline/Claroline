import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {GroupCard} from '#/main/core/user/data/components/group-card'

function getRoles(user) {
  return user.roles.map(role => trans(role.translationKey)).join(', ')
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
    open: (row) => ({
      type: LINK_BUTTON,
      target: `/groups/form/${row.id}`
    }),
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
        type: 'choice',
        alias: 'role',
        options: {
          choices: getWorkspaceRoles(workspace)
        },
        label: trans('roles'),
        displayed: true,
        filterable: true,
        render: (rowData) => getRoles(rowData)
      }
    ],
    card: GroupCard
  }
}

export {
  getGroupList
}
