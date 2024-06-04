import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/app/context/routing'

/**
 * Open the Public context.
 */
export default () => ({
  name: 'public',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-home',
  label: trans('open-public', {}, 'actions'),
  target: route('public'),
  scope: ['object'],
  group: trans('management')
})
