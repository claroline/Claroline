
import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'

import {declareAction} from '#/main/app/action'

export default declareAction((certificates) => {
  return ({
    name: 'download',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-download',
    label: trans('download', {}, 'actions'),
    displayed: hasPermission('open', certificates[0]),
    request: {
      url: ['apiv2_sequence_certificate_download', {certificateId: certificates[0].id}]
    },
    scope: ['object'],
    default: true
  })
})
