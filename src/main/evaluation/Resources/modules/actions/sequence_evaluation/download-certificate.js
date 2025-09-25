import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'

import {constants} from '#/main/evaluation/constants'
import {declareAction} from '#/main/app/action'

export default declareAction((evaluations) => {
  const processable = evaluations.filter(evaluation =>
    !!evaluation.certified
    && hasPermission('open', evaluation)
    && [constants.EVALUATION_STATUS_COMPLETED, constants.EVALUATION_STATUS_PASSED].includes(evaluation.status)
  )

  return ({
    name: 'download-certificate',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-file-pdf',
    label: processable.length > 1
      ? trans('download_certificates', {}, 'actions')
      : trans('download_certificate', {}, 'actions'),
    displayed: !isEmpty(processable),
    request: {
      url: ['apiv2_sequence_download_certificate'],
      request: {
        method: 'POST',
        body: JSON.stringify(processable.map(evaluation => evaluation.id))
      }
    },
    scope: ['object', 'collection'],
    group: trans('transfer')
  })
})
