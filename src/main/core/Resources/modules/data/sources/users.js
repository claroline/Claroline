import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/user/routing'
import {UserCard} from '#/main/core/user/components/card'

export default {
  name: 'users',
  icon: 'fa fa-fw fa-user',
  parameters: {
    primaryAction: (user) => ({
      type: URL_BUTTON,
      target: '#' + route(user)
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
        label: trans('disabled'),
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
        name: 'roleTranslation',
        type: 'string',
        label: trans('role'),
        displayed: false,
        filterable: true
      }
    ],
    card: UserCard
  }
}
