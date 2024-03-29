import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/resource/routing'

/**
 * Opens the edition form of a resource.
 * Permits to modify custom resource properties (to modify the node properties, it's the `configure` action).
 */
export default (resourceNodes, nodesRefresher, path) => ({
  name: 'edit',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-pencil',
  label: trans('edit', {}, 'actions'),
  primary: true,
  target: `${route(resourceNodes[0], path)}/edit`
})
