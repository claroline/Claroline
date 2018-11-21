import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

/**
 * Opens a resource node.
 *
 * @param {Array}  resourceNodes  - the list of resource nodes on which we want to execute the action.
 */
export default (resourceNodes) => ({
  name: 'open',
  type: URL_BUTTON,
  label: trans('open', {}, 'actions'),
  primary: true,
  icon: 'fa fa-fw fa-arrow-circle-o-right',
  target: ['claro_resource_open', {
    resourceType: resourceNodes[0].meta.type,
    node: resourceNodes[0].id
  }]
})
