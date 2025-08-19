import isEmpty from 'lodash/isEmpty'

import {declareAction} from '#/main/app/action'
import {trans} from '#/main/app/intl'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'

import {MODAL_REGISTRATION_PARAMETERS} from '#/plugin/cursus/registration/modals/parameters'

export default declareAction((registrations, refresher) => ({
  name: 'edit',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-pencil',
  label: trans('edit', {}, 'actions'),
  displayed: hasPermission('edit', registrations[0]) && !isEmpty(registrations[0].form),
  modal: [MODAL_REGISTRATION_PARAMETERS, {
    target: ['apiv2_training_session_user_update', {id: registrations[0].id}],
    registration: registrations[0],
    onSave: refresher.update
  }],
  group: trans('management'),
  scope: ['object']
}))
