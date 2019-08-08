import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

/**
 * Opens a resource node.
 *
 * @param {Array}  resourceNodes  - the list of resource nodes on which we want to execute the action.
 * @param {object} nodesRefresher
 * @param {string} path
 */
export default (resourceNodes, nodesRefresher, path) => ({
  name: 'open',
  type: LINK_BUTTON,
  label: trans('open', {}, 'actions'),
  default: true,
  icon: 'fa fa-fw fa-arrow-circle-o-right',
  target: `${path}/${resourceNodes[0].meta.slug}`
})
