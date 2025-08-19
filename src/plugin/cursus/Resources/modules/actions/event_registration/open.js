import {declareAction} from '#/main/app/action'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

import {MODAL_REGISTRATION_ABOUT} from '#/plugin/cursus/registration/modals/about'

export default declareAction((registrations) => ({
  name: 'open',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-arrow-up-right-from-square',
  label: trans('open', {}, 'actions'),
  displayed: hasPermission('open', registrations[0]),
  modal: [MODAL_REGISTRATION_ABOUT, {
    registration: registrations[0]
  }],
  scope: ['object'],
  default: true
}))
