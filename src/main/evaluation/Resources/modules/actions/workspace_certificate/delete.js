import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

import {hasPermission} from '#/main/app/security'
import {declareAction} from '#/main/app/action'

export default declareAction((certificates, refresher) => {
  const processable = certificates.filter(certificate => hasPermission('administrate', certificate))

  return {
    name: 'delete',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-trash',
    label: trans('delete', {}, 'actions'),
    displayed: 0 !== processable.length,
    dangerous: true,
    confirm: {
      message: transChoice('certificates_delete_confirm', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'evaluation'),
      additional: trans('irreversible_action_confirm'),
      items:  processable.map(item => ({
        thumbnail: get(item, 'user.picture'),
        id: get(item, 'user.id'),
        name: get(item, 'user.name')
      }))
    },
    request: {
      url: ['apiv2_workspace_certificate_delete'],
      request: {
        method: 'DELETE',
        body: JSON.stringify(processable.map(evaluation => evaluation.id))
      },
      success: () => refresher.delete(processable)
    },
    group: trans('management')
  }
})
