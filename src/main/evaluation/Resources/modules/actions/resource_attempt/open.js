import {declareAction, constants} from '#/main/app/action'
import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'

import {MODAL_RESOURCE_USER_ATTEMPT} from '#/main/evaluation/resource/modals/user-attempt'

export default declareAction((attempts) => ({
  name: 'open',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-arrow-up-right-from-square',
  label: trans('open', {}, 'actions'),
  displayed: hasPermission('open', attempts[0]),
  scope: ['object'],
  modal: [MODAL_RESOURCE_USER_ATTEMPT, {
    evaluation: attempts[0]
  }],
  set: [constants.ACTION_SET_LIST]
}))
