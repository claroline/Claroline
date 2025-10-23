import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'
import {declareAction} from '#/main/app/action'

import {constants} from '#/main/evaluation/constants'

export default declareAction((evaluations) => {
  const processable = evaluations.filter(evaluation =>
    !!evaluation.certified
    && hasPermission('administrate', evaluation)
    && constants.EVALUATION_TERMINATED_STATUSES.includes(evaluation.status)
  )

  return ({
    name: 'regenerate-certificate',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-file-export',
    label: processable.length > 1
      ? trans('regenerate_certificates', {}, 'actions')
      : trans('regenerate_certificate', {}, 'actions'),
    request: {
      url: ['apiv2_workspace_regenerate_certificate'],
      request: {
        method: 'POST',
        body: JSON.stringify(processable.map(evaluation => evaluation.id))
      }
    },
    displayed: !isEmpty(processable),
    scope: ['object', 'collection'],
    group: trans('management')
  })
})
