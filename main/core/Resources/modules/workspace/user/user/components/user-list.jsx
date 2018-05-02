import {trans} from '#/main/core/translation'

import {UserCard} from '#/main/core/user/data/components/user-card'

function getRoles(user, workspace) {
  return user.roles.filter(role => role.workspace && role.workspace.id === workspace.uuid).map(role => trans(role.translationKey)).join(', ')
}

function getGroups(workspace) {
  return workspace.groups.map(group => group.name).join(', ')
}

function getWorkspaceRoles(workspace) {
  const roles = {}

  workspace.roles.forEach(role => {
    roles[role.id] = role.translationKey
  })

  return roles
}

function getWorkspaceGroups(workspace) {
  const groups = {}

  workspace.groups.forEach(group => {
    groups[group.id] = group.translationKey
  })

  return groups
}

function getUserList(workspace) {
  return {
    open: (row) => ({
      type: 'url',
      target: ['claro_user_profile', {publicUrl: row.meta.publicUrl}]
    }),
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
        type: 'choice',
        alias: 'role',
        options: {
          choices: getWorkspaceRoles(workspace)
        },
        label: trans('roles'),
        displayed: true,
        filterable: true,
        renderer: (rowData) => getRoles(rowData, workspace)
      }, {
        name: 'groups',
        type: 'choice',
        alias: 'group',
        options: {
          choices: getWorkspaceGroups(workspace)
        },
        label: trans('groups'),
        displayed: true,
        filterable: true,
        renderer: (rowData) => getGroups(rowData, workspace)
      }
    ],
    card: UserCard
  }
}

export {
  getUserList
}
