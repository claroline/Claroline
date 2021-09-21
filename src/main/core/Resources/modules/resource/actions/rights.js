import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_RESOURCE_RIGHTS} from '#/main/core/resource/modals/rights'

/**
 * Displays a form to configure the rights of some resource nodes.
 *
 * @param {Array}  resourceNodes  - the list of resource nodes on which we want to execute the action.
 * @param {object} nodesRefresher - an object containing methods to update context in response to action (eg. add, update, delete).
 */
export default (resourceNodes, nodesRefresher) => ({ // todo collection
  name: 'rights',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-lock',
  label: trans('edit-rights', {}, 'actions'),
  modal: [MODAL_RESOURCE_RIGHTS, {
    resourceNode: 1 === resourceNodes.length && resourceNodes[0],
    updateNode: (resourceNode) => nodesRefresher.update([resourceNode])
  }]
})
