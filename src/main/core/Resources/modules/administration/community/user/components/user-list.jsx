import cloneDeep from 'lodash/cloneDeep'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {UserCard} from '#/main/core/user/components/card'

const UserList = {
  open: (row) => ({
    type: LINK_BUTTON,
    target: `/users/form/${row.id}`,
    label: trans('edit', {}, 'actions')
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
      type: 'email',
      label: trans('email'),
      displayed: true
    }, {
      name: 'administrativeCode',
      type: 'string',
      label: trans('code')
    }, {
      name: 'meta.personalWorkspace',
      alias: 'hasPersonalWorkspace',
      type: 'boolean',
      label: trans('has_personal_workspace')
    }, {
      name: 'restrictions.disabled',
      alias: 'isDisabled',
      type: 'boolean',
      label: trans('disabled')
    }, {
      name: 'meta.created',
      type: 'date',
      alias: 'created',
      label: trans('creation_date'),
      filterable: false
    }, {
      name: 'meta.lastActivity',
      type: 'date',
      alias: 'lastActivity',
      label: trans('last_activity'),
      displayed: true,
      filterable: false,
      options: {
        time: true
      }
    }, {
      name: 'group_name',
      label: trans('group'),
      type: 'string',
      displayed: false
    }, {
      name: 'unionOrganizationName',
      label: trans('organization'),
      type: 'string',
      displayed: false
    }
  ],
  card: UserCard
}

const getUserListDefinition = (searchData) => {
  const def = cloneDeep(UserList.definition)

  if (searchData.platformRoles) {
    const platformChoices = {}
    searchData.platformRoles.forEach(role => {
      platformChoices[role.id] = role.translationKey
    })

    def.push({
      name: 'role',
      label: trans('role'),
      type: 'choice',
      displayed: false,
      options: {
        choices: platformChoices
      }
    })
  }

  return def
}

export {
  UserList,
  getUserListDefinition
}
