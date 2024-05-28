import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/app/context/routing'

export default (contexts) => ({
  name: 'configure',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-cog',
  label: trans('configure', {}, 'actions'),
  displayed: -1 !== contexts.findIndex(context => hasPermission('administrate', context)),
  target: `${route(contexts[0].type, contexts[0].slug)}/edit`,
  group: trans('management'),
  scope: ['object']
})
