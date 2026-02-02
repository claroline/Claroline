import {constants, declareAction} from '#/main/app/action'
import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_WORKSPACE_USER_PROGRESSION} from '#/main/evaluation/workspace/modals/user-progression'

export default declareAction((evaluations) => ({
  name: 'open',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-arrow-up-right-from-square',
  label: trans('open', {}, 'actions'),
  displayed: hasPermission('open', evaluations[0]),
  scope: ['object'],
  modal: [MODAL_WORKSPACE_USER_PROGRESSION, {
    evaluation: evaluations[0]
  }],
  set: [constants.ACTION_SET_LIST]
}))
