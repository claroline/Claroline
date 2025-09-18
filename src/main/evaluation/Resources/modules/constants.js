
// evaluation
import {trans} from '#/main/app/intl'

const EVALUATION_STATUS_NOT_ATTEMPTED = 'not_attempted'
const EVALUATION_STATUS_UNKNOWN       = 'unknown'
const EVALUATION_STATUS_INCOMPLETE    = 'incomplete'
const EVALUATION_STATUS_COMPLETED     = 'completed'
const EVALUATION_STATUS_PENDING       = 'pending'
const EVALUATION_STATUS_FAILED        = 'failed'
const EVALUATION_STATUS_PASSED        = 'passed'

const EVALUATION_STATUS_PRIORITY = {
  [EVALUATION_STATUS_NOT_ATTEMPTED]: 0,
  [EVALUATION_STATUS_UNKNOWN]:       1,
  [EVALUATION_STATUS_INCOMPLETE]:    3,
  [EVALUATION_STATUS_FAILED]:        5,
  [EVALUATION_STATUS_COMPLETED]:     6,
  [EVALUATION_STATUS_PENDING]:       7,
  [EVALUATION_STATUS_PASSED]:        8
}

const EVALUATION_STATUS_COLOR = {
  [EVALUATION_STATUS_NOT_ATTEMPTED]: 'secondary',
  [EVALUATION_STATUS_UNKNOWN]:       'secondary',
  [EVALUATION_STATUS_INCOMPLETE]:    'info',
  [EVALUATION_STATUS_COMPLETED]:     'success',
  [EVALUATION_STATUS_PENDING]:       'warning',
  [EVALUATION_STATUS_FAILED]:        'danger',
  [EVALUATION_STATUS_PASSED]:        'success'
}

const EVALUATION_TERMINATED_STATUSES = [
  EVALUATION_STATUS_COMPLETED,
  EVALUATION_STATUS_FAILED,
  EVALUATION_STATUS_PASSED
]

const EVALUATION_STATUSES = {
  [EVALUATION_STATUS_NOT_ATTEMPTED]: trans('evaluation_not_attempted_status', {}, 'evaluation'),
  [EVALUATION_STATUS_UNKNOWN]:       trans('evaluation_unknown_status', {}, 'evaluation'),
  [EVALUATION_STATUS_INCOMPLETE]:    trans('evaluation_incomplete_status', {}, 'evaluation'),
  [EVALUATION_STATUS_COMPLETED]:     trans('evaluation_completed_status', {}, 'evaluation'),
  [EVALUATION_STATUS_PENDING]:       trans('evaluation_pending_status', {}, 'evaluation'),
  [EVALUATION_STATUS_FAILED]:        trans('evaluation_failed_status', {}, 'evaluation'),
  [EVALUATION_STATUS_PASSED]:        trans('evaluation_passed_status', {}, 'evaluation')
}

const EVALUATION_STATUSES_SHORT = {
  [EVALUATION_STATUS_NOT_ATTEMPTED]: trans('evaluation_not_attempted_short', {}, 'evaluation'),
  [EVALUATION_STATUS_UNKNOWN]:       trans('evaluation_unknown_short', {}, 'evaluation'),
  [EVALUATION_STATUS_INCOMPLETE]:    trans('evaluation_incomplete_short', {}, 'evaluation'),
  [EVALUATION_STATUS_COMPLETED]:     trans('evaluation_completed_short', {}, 'evaluation'),
  [EVALUATION_STATUS_PENDING]:       trans('evaluation_pending_short', {}, 'evaluation'),
  [EVALUATION_STATUS_FAILED]:        trans('evaluation_failed_short', {}, 'evaluation'),
  [EVALUATION_STATUS_PASSED]:        trans('evaluation_passed_short', {}, 'evaluation')
}

export const constants = {
  // evaluation
  EVALUATION_STATUS_PRIORITY,
  EVALUATION_STATUS_COLOR,
  EVALUATION_STATUSES,
  EVALUATION_STATUSES_SHORT,
  EVALUATION_TERMINATED_STATUSES,

  EVALUATION_STATUS_NOT_ATTEMPTED,
  EVALUATION_STATUS_UNKNOWN,
  EVALUATION_STATUS_INCOMPLETE,
  EVALUATION_STATUS_PENDING,
  EVALUATION_STATUS_COMPLETED,
  EVALUATION_STATUS_PASSED,
  EVALUATION_STATUS_FAILED
}
