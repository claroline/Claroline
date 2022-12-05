import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_RESOURCE_ABOUT} from '#/main/core/resource/modals/about'

/**
 * Displays a general information about a resource node.
 *
 * @param {Array}  resourceNodes  - the list of resource nodes on which we want to execute the action.
 */
export default (resourceNodes) => ({
  name: 'about',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-circle-info',
  label: trans('show-info', {}, 'actions'),
  modal: [MODAL_RESOURCE_ABOUT, {
    resourceNode: resourceNodes[0]
  }]
})
