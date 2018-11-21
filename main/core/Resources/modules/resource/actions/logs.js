import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

// todo : move in a plugin

/**
 * Displays some analytics data about a resource node.
 *
 * @param {Array}  resourceNodes  - the list of resource nodes on which we want to execute the action.
 */
export default (resourceNodes) => ({
  name: 'logs',
  type: URL_BUTTON, // TODO : it will be section
  icon: 'fa fa-fw fa-line-chart',
  label: trans('show-logs', {}, 'actions'),
  target: ['claro_resource_logs', {node: resourceNodes[0].id}]
})
