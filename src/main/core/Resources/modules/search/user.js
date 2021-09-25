import {trans} from '#/main/app/intl'
import {UserCard} from '#/main/core/user/components/card'
import {route} from '#/main/core/user/routing'

export default {
  name: 'user',
  label: trans('users'),
  component: UserCard,
  link: (result) => route(result)
}
