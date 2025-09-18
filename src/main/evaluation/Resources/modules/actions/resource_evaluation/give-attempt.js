import isEmpty from 'lodash/isEmpty'

import {declareAction} from '#/main/app/action'
import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'

import {supportAttempts} from '#/main/core/resource/utils'
import {constants} from '#/main/evaluation/constants'

export default declareAction((evaluations, refresher) => {
  const processable = evaluations.filter(evaluation =>
    supportAttempts(evaluation.resourceNode)
    && 0 < evaluation.nbAttempts
    && hasPermission('administrate', evaluation)
    && [constants.EVALUATION_STATUS_COMPLETED, constants.EVALUATION_STATUS_PASSED, constants.EVALUATION_STATUS_FAILED].includes(evaluation.status)
  )

  return ({
    name: 'give-attempt',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-repeat',
    label: trans('give_attempt', {}, 'actions'),
    request: {
      url: ['apiv2_resource_evaluation_give_attempt'],
      request: {
        method: 'PUT',
        body: JSON.stringify(processable.map(evaluation => evaluation.id))
      },
      success: () => refresher.update(processable)
    },
    displayed: !isEmpty(processable),
    scope: ['object', 'collection'],
    group: trans('management')
  })
})
