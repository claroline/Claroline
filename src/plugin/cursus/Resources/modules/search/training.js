import {trans} from '#/main/app/intl'

import {route} from '#/plugin/cursus/course/routing'

export default {
  name: 'training',
  label: trans('courses', {}, 'cursus'),
  link: (result) => route(result)
}
