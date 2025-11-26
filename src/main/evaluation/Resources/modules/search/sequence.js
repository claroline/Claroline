import {trans} from '#/main/app/intl'
import {route} from '#/main/evaluation/sequence/routing'

export default {
  name: 'sequence',
  label: trans('sequences', {}, 'evaluation'),
  link: route
}
