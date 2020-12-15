import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_RESOURCE_PARAMETERS} from '#/main/core/resource/modals/parameters'

/**
 * Displays a form to modify resource node properties.
 *
 * @param {Array}  resourceNodes  - the list of resource nodes on which we want to execute the action.
 * @param {object} nodesRefresher - an object containing methods to update context in response to action (eg. add, update, delete).
 */
export default (resourceNodes, nodesRefresher) => ({ // todo collection
  name: 'configure',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-cog',
  label: trans('configure', {}, 'actions'),
  modal: [MODAL_RESOURCE_PARAMETERS, {
    resourceNode: 1 === resourceNodes.length && resourceNodes[0],
    updateNode: (resourceNode) => nodesRefresher.update([resourceNode])
  }]
})
