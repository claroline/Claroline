import {trans} from '#/main/core/translation'

import {UserCard} from '#/main/core/administration/user/user/components/user-card.jsx'

function getRoles(user, workspace) {
  return user.roles.filter(role => role.workspace && role.workspace.id === workspace.uuid).map(role => trans(role.translationKey)).join(', ')
}

function getWorkspaceRoles(workspace) {
  const roles = {}

  workspace.roles.forEach(role => {
    roles[role.id] = role.translationKey
  })

  return roles
}

function getUserList(workspace)
{
  return {
    open: {
      action: (row) => `#/users/form/${row.id}`
    },
    definition: [
      {
        name: 'username',
        type: 'username',
        label: trans('username'),
        displayed: true,
        primary: true
      }, {
        name: 'lastName',
        type: 'string',
        label: trans('last_name'),
        displayed: true
      }, {
        name: 'firstName',
        type: 'string',
        label: trans('first_name'),
        displayed: true
      }, {
        name: 'email',
        alias: 'mail',
        type: 'email',
        label: trans('email'),
        displayed: true
      }, {
        name: 'administrativeCode',
        type: 'string',
        label: trans('code')
      }, {
        name: 'meta.lastLogin',
        type: 'date',
        alias: 'lastLogin',
        label: trans('last_login'),
        displayed: true,
        options: {
          time: true
        }
      }, {
        name: 'roles',
        type: 'enum',
        alias: 'role',
        options: {
          choices: getWorkspaceRoles(workspace)
        },
        label: trans('roles'),
        displayed: true,
        filterable: true,
        renderer: (rowData) => getRoles(rowData, workspace)
      }
    ],
    card: UserCard
  }
}

export {
  getUserList
}
