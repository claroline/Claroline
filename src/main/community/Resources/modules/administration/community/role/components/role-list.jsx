import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {constants} from '#/main/community/constants'
import {RoleCard} from '#/main/core/user/data/components/role-card'

const RoleList = {
  open: (row) => ({
    type: LINK_BUTTON,
    target: `/roles/form/${row.id}`,
    label: trans('edit', {}, 'actions')
  }),
  definition: [
    {
      name: 'translationKey',
      type: 'translation',
      label: trans('name'),
      displayed: true
    }, {
      name: 'name',
      type: 'string',
      label: trans('code'),
      displayed: false,
      primary: true
    }, {
      name: 'type',
      type: 'choice',
      label: trans('type'),
      options: {
        choices: constants.ROLE_TYPES
      },
      displayed: true
    }, {
      name: 'workspace',
      type: 'workspace',
      label: trans('workspace'),
      displayed: true,
      filterable: true
    }, {
      name: 'user',
      type: 'user',
      label: trans('user'),
      displayed: true,
      filterable: false,
      options: {
        placeholder: false
      }
    }
  ],

  card: RoleCard
}

export {
  RoleList
}
