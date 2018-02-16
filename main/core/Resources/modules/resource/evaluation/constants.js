import {trans} from '#/main/core/translation'

const EVALUATION_STATUS_NOT_ATTEMPTED = 'not_attempted'
const EVALUATION_STATUS_UNKNOWN       = 'unknown'
const EVALUATION_STATUS_OPENED        = 'opened'
const EVALUATION_STATUS_INCOMPLETE    = 'incomplete'
const EVALUATION_STATUS_PARTICIPATED  = 'participated'
const EVALUATION_STATUS_COMPLETED     = 'completed'
const EVALUATION_STATUS_PASSED        = 'passed'
const EVALUATION_STATUS_FAILED        = 'failed'

const EVALUATION_STATUSES = {
  [EVALUATION_STATUS_NOT_ATTEMPTED]: trans('evaluation_not_attempted_status', {}, 'evaluation'),
  [EVALUATION_STATUS_UNKNOWN]:       trans('evaluation_unknown_status', {}, 'evaluation'),
  [EVALUATION_STATUS_OPENED]:        trans('evaluation_opened_status', {}, 'evaluation'),
  [EVALUATION_STATUS_INCOMPLETE]:    trans('evaluation_incomplete_status', {}, 'evaluation'),
  [EVALUATION_STATUS_PARTICIPATED]:  trans('evaluation_participated_status', {}, 'evaluation'),
  [EVALUATION_STATUS_COMPLETED]:     trans('evaluation_completed_status', {}, 'evaluation'),
  [EVALUATION_STATUS_PASSED]:        trans('evaluation_passed_status', {}, 'evaluation'),
  [EVALUATION_STATUS_FAILED]:        trans('evaluation_failed_status', {}, 'evaluation')
}

const EVALUATION_STATUS_PRIORITY = {
  [EVALUATION_STATUS_NOT_ATTEMPTED]: 0,
  [EVALUATION_STATUS_UNKNOWN]:       1,
  [EVALUATION_STATUS_OPENED]:        2,
  [EVALUATION_STATUS_INCOMPLETE]:    3,
  [EVALUATION_STATUS_PARTICIPATED]:  4,
  [EVALUATION_STATUS_COMPLETED]:     5,
  [EVALUATION_STATUS_PASSED]:        6,
  [EVALUATION_STATUS_FAILED]:        7
}

export const constants = {
  EVALUATION_STATUSES,
  EVALUATION_STATUS_PRIORITY,
  EVALUATION_STATUS_NOT_ATTEMPTED,
  EVALUATION_STATUS_UNKNOWN,
  EVALUATION_STATUS_OPENED,
  EVALUATION_STATUS_INCOMPLETE,
  EVALUATION_STATUS_PARTICIPATED,
  EVALUATION_STATUS_COMPLETED,
  EVALUATION_STATUS_PASSED,
  EVALUATION_STATUS_FAILED
}
