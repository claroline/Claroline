import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_DOCUMENTATION} from '#/plugin/documentation/modals/documentation'

/**
 * Displays documentation for the workspace.
 */
export default () => ({
  name: 'documentation',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-question-circle',
  label: trans('show-documentation', {}, 'actions'),
  modal: [MODAL_DOCUMENTATION, {
    tags: ['workspace']
  }],
  group: trans('help')
})
