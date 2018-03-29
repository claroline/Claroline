import {trans} from '#/main/core/translation'

import {UserCard} from '#/main/core/user/data/components/user-card'

const UserList = {
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
    }
  ],
  card: UserCard
}

export {
  UserList
}
