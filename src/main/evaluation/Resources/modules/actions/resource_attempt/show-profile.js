import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/community/user/routing'
import {constants, declareAction} from '#/main/app/action'

export default declareAction((attempts) => ({
  name: 'show-profile',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-id-card',
  label: trans('show_profile', {}, 'actions'),
  target: route(get(attempts[0], 'user')),
  scope: [constants.ACTION_SCOPE_OBJECT],
  group: trans('community'),
  set: [constants.ACTION_SET_LIST]
}))
