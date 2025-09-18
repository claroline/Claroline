import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/community/user/routing'
import {declareAction} from '#/main/app/action'

export default declareAction((evaluations) => ({
  name: 'show-profile',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-id-card',
  label: trans('show_profile', {}, 'actions'),
  target: route(get(evaluations[0], 'user')),
  scope: ['object'],
  group: trans('community')
}))
