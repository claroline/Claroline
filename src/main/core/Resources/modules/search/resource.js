import {trans} from '#/main/app/intl'
import {ResourceCard} from '#/main/core/resource/components/card'
import {route} from '#/main/core/resource/routing'

export default {
  name: 'resource',
  label: trans('resources'),
  component: ResourceCard,
  link: route
}
