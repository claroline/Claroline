import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {route as userRoute} from '#/main/community/user/routing'
import {UserCard} from '#/main/community/user/components/card'

export default {
  name: 'teams-members',
  icon: 'fa fa-fw fa-user-plus',
  parameters: {
    primaryAction: (user) => ({
      type: URL_BUTTON,
      target: '#' + userRoute(user)
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
        name: 'meta.lastActivity',
        type: 'date',
        alias: 'lastActivity',
        label: trans('last_activity'),
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
      }, {
        name: 'team',
        type: 'team',
        label: trans('team', {}, 'data_sources'),
        displayed: true,
        filterable: true
      }
    ],
    card: UserCard
  }
}
