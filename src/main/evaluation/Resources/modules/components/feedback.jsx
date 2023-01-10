import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {ContentHtml} from '#/main/app/content/components/html'

import {constants} from '#/main/evaluation/constants'

/**
 * Display a custom message based on the status of an evaluation.
 */
const EvaluationFeedback = props => {
  const displayed = [
    constants.EVALUATION_STATUS_PASSED,
    constants.EVALUATION_STATUS_FAILED,
    constants.EVALUATION_STATUS_COMPLETED
  ].indexOf(props.status) > -1 // Evaluation is finished

  if (displayed) {
    let alertType
    let alertTitle
    let alertMessage
    switch (props.status) {
      case constants.EVALUATION_STATUS_PASSED:
        alertType = 'success'
        alertTitle = trans('evaluation_passed_feedback', {}, 'evaluation')
        alertMessage = props.success || trans('evaluation_passed_feedback_msg', {}, 'evaluation')
        break
      case constants.EVALUATION_STATUS_FAILED:
        alertType = 'danger'
        alertTitle = trans('evaluation_failed_feedback', {}, 'evaluation')
        alertMessage = props.failure || trans('evaluation_failed_feedback_msg', {}, 'evaluation')
        break
      case constants.EVALUATION_STATUS_COMPLETED:
      default:
        alertType = 'info'
        alertTitle = trans('evaluation_completed_feedback', {}, 'evaluation')
        alertMessage = trans('evaluation_completed_feedback_msg', {}, 'evaluation')
        break
    }

    return (
      <AlertBlock
        type={alertType}
        title={alertTitle}
      >
        <ContentHtml>{alertMessage}</ContentHtml>
      </AlertBlock>
    )
  }

  return null // feedback not available
}

EvaluationFeedback.propTypes = {
  status: T.string,
  success: T.string,
  failure: T.string
}

EvaluationFeedback.defaultProps = {
  status: constants.EVALUATION_STATUS_NOT_ATTEMPTED
}

export {
  EvaluationFeedback
}
