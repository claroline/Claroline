import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/resource'

/**
 * Displays a form to modify resource node properties.
 */
export default (resourceNodes, nodesRefresher, path) => ({
  name: 'configure',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-cog',
  label: trans('configure', {}, 'actions'),
  target: `${route(resourceNodes[0], path)}/edit`
})
