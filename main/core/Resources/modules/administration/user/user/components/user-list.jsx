import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {UserCard} from '#/main/core/user/data/components/user-card'

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
      label: trans('user_disabled'),
      displayed: true
    }, {
      name: 'meta.created',
      type: 'date',
      alias: 'created',
      label: trans('creation_date')
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
      name: 'group_name',
      label: trans('group'),
      type: 'string',
      displayed: false
    }
  ],
  card: UserCard
}

const getUserListDefinition = (searchData) => {
  const def = UserList.definition

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

  //  console.log(def)

  return def
}

export {
  UserList,
  getUserListDefinition
}
