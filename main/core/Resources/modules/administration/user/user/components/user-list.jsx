import {t} from '#/main/core/translation'

import {UserCard} from '#/main/core/administration/user/user/components/user-card.jsx'

const UserList = {
  open: {
    action: (row) => `#/users/form/${row.id}`
  },
  definition: [
    {
      name: 'username',
      type: 'username',
      label: t('username'),
      displayed: true,
      primary: true
    }, {
      name: 'lastName',
      type: 'string',
      label: t('last_name'),
      displayed: true
    }, {
      name: 'firstName',
      type: 'string',
      label: t('first_name'),
      displayed: true
    }, {
      name: 'email',
      alias: 'mail',
      type: 'email',
      label: t('email'),
      displayed: true
    }, {
      name: 'administrativeCode',
      type: 'string',
      label: t('code')
    }, {
      name: 'meta.personalWorkspace',
      alias: 'hasPersonalWorkspace',
      type: 'boolean',
      label: t('has_personal_workspace')
    }, {
      name: 'restrictions.disabled',
      alias: 'isDisabled',
      type: 'boolean',
      label: t('user_disabled'),
      displayed: true
    }, {
      name: 'meta.created',
      type: 'date',
      alias: 'created',
      label: t('creation_date')
    },
    {
      name: 'meta.lastLogin',
      type: 'date',
      alias: 'lastLogin',
      label: t('last_login'),
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
