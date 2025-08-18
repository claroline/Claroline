import {trans} from '#/main/app/intl'
import {DataMicro} from '#/main/app/data/components/micro'

import {route} from '#/main/community/user/routing'
import {createElement} from 'react'

export default {
  name: 'user',
  label: trans('users'),
  component: ({object}) => createElement(DataMicro, {
    object: {
      thumbnail: object.picture,
      id: object.id,
      name: object.name
    }
  }),
  link: (result) => route(result)
}
