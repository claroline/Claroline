import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_DOCUMENTATION} from '#/plugin/documentation/modals/documentation'

/**
 * Displays documentation for the resource type.
 *
 * @param {Array}  resourceNodes  - the list of resource nodes on which we want to execute the action.
 */
export default (resourceNodes) => ({
  name: 'documentation',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-question-circle',
  label: trans('show-documentation', {}, 'actions'),
  modal: [MODAL_DOCUMENTATION, {
    tags: ['resource', resourceNodes[0].meta.type]
  }],
  group: trans('help')
})
