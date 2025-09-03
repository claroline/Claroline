import get from 'lodash/get'

import {declareAction} from '#/main/app/action'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

import {hasPermission} from '#/main/app/security'

export default declareAction((sessions) => {
  let confirmAdditional = ''
  if (get(sessions[0], 'registration.userValidation', false)) {
    confirmAdditional += trans('register_workspace_additional_confirmed', {}, 'actions')
  }

  if (get(sessions[0], 'registration.validation', false)) {
    if (confirmAdditional) {
      confirmAdditional += '<br/>'
    }
    confirmAdditional += trans('register_workspace_additional_validated', {}, 'actions')
  }

  return ({
    name: 'register-workspace',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-book',
    label: trans('register_workspace', {}, 'actions'),
    displayed: !!sessions[0].workspace && !get(sessions[0], 'meta.canceled', false) && hasPermission('follow', sessions[0]),
    request: {
      url: ['apiv2_cursus_session_register_workspace', {id: sessions[0].id}],
      request: {
        method: 'POST'
      }
    },
    confirm: {
      message: trans('register_workspace_confirm', {}, 'actions'),
      additional: confirmAdditional
    },
    scope: ['object'],
    group: trans('management')
  })
})
