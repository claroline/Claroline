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
