import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {UserCard} from '#/main/core/user/components/card'

const AssertionList = {
  open: (row) => ({
    type: LINK_BUTTON,
    target: `/badges/assertion/${row.id}`,
    label: trans('', {}, 'actions')
  }),
  definition: [
    {
      name: 'user.username',
      type: 'username',
      label: trans('username'),
      displayed: true,
      primary: true
    }, {
      name: 'user.lastName',
      type: 'string',
      label: trans('last_name'),
      displayed: true
    }, {
      name: 'user.firstName',
      type: 'string',
      label: trans('first_name'),
      displayed: true
    }, {
      name: 'user.email',
      type: 'email',
      label: trans('email'),
      displayed: true
    }
  ],
  card: UserCard
}

export {
  AssertionList
}
