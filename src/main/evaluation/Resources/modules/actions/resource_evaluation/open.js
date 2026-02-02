import {declareAction, constants} from '#/main/app/action'
import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'

import {MODAL_RESOURCE_USER_PROGRESSION} from '#/main/evaluation/resource/modals/user-progression'

export default declareAction((evaluations) => ({
  name: 'open',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-arrow-up-right-from-square',
  label: trans('open', {}, 'actions'),
  displayed: hasPermission('open', evaluations[0]),
  scope: ['object'],
  modal: [MODAL_RESOURCE_USER_PROGRESSION, {
    evaluation: evaluations[0]
  }],
  set: [constants.ACTION_SET_LIST]
}))
