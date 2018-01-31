import {createSelector} from 'reselect'
import isEmpty from 'lodash/isEmpty'

import {constants} from '#/main/core/resource/evaluation/constants'

const evaluation = state => state.evaluation
const evaluationStatus = createSelector(
  [evaluation],
  (evaluation) => evaluation.status
)

const hasEvaluation = createSelector(
  [evaluation],
  (evaluation) => !isEmpty(evaluation)
)

const isTerminated = createSelector(
  [evaluationStatus],
  (evaluationStatus) => [
    constants.EVALUATION_STATUS_COMPLETED,
    constants.EVALUATION_STATUS_PASSED,
    constants.EVALUATION_STATUS_FAILED
  ].inArray(evaluationStatus)
)

const isSuccessful = createSelector(
  [evaluationStatus],
  (evaluationStatus) => [
    constants.EVALUATION_STATUS_COMPLETED,
    constants.EVALUATION_STATUS_PASSED
  ].inArray(evaluationStatus)
)

export const select = {
  evaluation,
  hasEvaluation,
  isTerminated,
  isSuccessful
}
