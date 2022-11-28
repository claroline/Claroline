import {trans} from '#/main/app/intl'
import {UserCard} from '#/main/community/user/components/card'
import {route} from '#/main/community/user/routing'

export default {
  name: 'user',
  label: trans('users'),
  component: UserCard,
  link: (result) => route(result)
}
