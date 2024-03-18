import React from 'react'
import {PropTypes as T} from 'prop-types'

import {constants} from '#/main/evaluation/constants'

const EvaluationStatus = (props) => {
  let status = props.status
  if (!status) {
    status = constants.EVALUATION_STATUS_UNKNOWN
  }

  return (
    <span className={`evaluation-status badge text-bg-${constants.EVALUATION_STATUS_COLOR[status]}`}>
      {constants.EVALUATION_STATUSES_SHORT[status]}
    </span>
  )
}

EvaluationStatus.propTypes = {
  status: T.string
}

export {
  EvaluationStatus
}
