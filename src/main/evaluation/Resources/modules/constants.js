
// evaluation
import {trans} from '#/main/app/intl'

const EVALUATION_STATUS_NOT_ATTEMPTED = 'not_attempted'
const EVALUATION_STATUS_TODO          = 'todo'
const EVALUATION_STATUS_UNKNOWN       = 'unknown'
const EVALUATION_STATUS_OPENED        = 'opened'
const EVALUATION_STATUS_INCOMPLETE    = 'incomplete'
const EVALUATION_STATUS_PARTICIPATED  = 'participated'
const EVALUATION_STATUS_FAILED        = 'failed'
const EVALUATION_STATUS_COMPLETED     = 'completed'
const EVALUATION_STATUS_PASSED        = 'passed'

const EVALUATION_STATUS_PRIORITY = {
  [EVALUATION_STATUS_NOT_ATTEMPTED]: 0,
  [EVALUATION_STATUS_TODO]:          0,
  [EVALUATION_STATUS_UNKNOWN]:       1,
  [EVALUATION_STATUS_OPENED]:        2,
  [EVALUATION_STATUS_INCOMPLETE]:    3,
  [EVALUATION_STATUS_PARTICIPATED]:  4,
  [EVALUATION_STATUS_FAILED]:        5,
  [EVALUATION_STATUS_COMPLETED]:     6,
  [EVALUATION_STATUS_PASSED]:        7
}

const EVALUATION_STATUS_COLOR = {
  [EVALUATION_STATUS_NOT_ATTEMPTED]: 'default',
  [EVALUATION_STATUS_TODO]:          'default',
  [EVALUATION_STATUS_UNKNOWN]:       'default',
  [EVALUATION_STATUS_OPENED]:        'warning',
  [EVALUATION_STATUS_INCOMPLETE]:    'warning',
  [EVALUATION_STATUS_PARTICIPATED]:  'success',
  [EVALUATION_STATUS_FAILED]:        'danger',
  [EVALUATION_STATUS_COMPLETED]:     'info',
  [EVALUATION_STATUS_PASSED]:        'success'
}

const EVALUATION_STATUSES = {
  [EVALUATION_STATUS_NOT_ATTEMPTED]: trans('evaluation_not_attempted_status', {}, 'evaluation'),
  [EVALUATION_STATUS_TODO]:          trans('evaluation_todo_status', {}, 'evaluation'),
  [EVALUATION_STATUS_UNKNOWN]:       trans('evaluation_unknown_status', {}, 'evaluation'),
  [EVALUATION_STATUS_OPENED]:        trans('evaluation_opened_status', {}, 'evaluation'),
  [EVALUATION_STATUS_INCOMPLETE]:    trans('evaluation_incomplete_status', {}, 'evaluation'),
  [EVALUATION_STATUS_PARTICIPATED]:  trans('evaluation_participated_status', {}, 'evaluation'),
  [EVALUATION_STATUS_FAILED]:        trans('evaluation_failed_status', {}, 'evaluation'),
  [EVALUATION_STATUS_COMPLETED]:     trans('evaluation_completed_status', {}, 'evaluation'),
  [EVALUATION_STATUS_PASSED]:        trans('evaluation_passed_status', {}, 'evaluation')
}

const EVALUATION_STATUSES_SHORT = {
  [EVALUATION_STATUS_NOT_ATTEMPTED]: trans('evaluation_not_attempted_short', {}, 'evaluation'),
  [EVALUATION_STATUS_TODO]:          trans('evaluation_todo_short', {}, 'evaluation'),
  [EVALUATION_STATUS_UNKNOWN]:       trans('evaluation_unknown_short', {}, 'evaluation'),
  [EVALUATION_STATUS_OPENED]:        trans('evaluation_opened_short', {}, 'evaluation'),
  [EVALUATION_STATUS_INCOMPLETE]:    trans('evaluation_incomplete_short', {}, 'evaluation'),
  [EVALUATION_STATUS_PARTICIPATED]:  trans('evaluation_participated_short', {}, 'evaluation'),
  [EVALUATION_STATUS_FAILED]:        trans('evaluation_failed_short', {}, 'evaluation'),
  [EVALUATION_STATUS_COMPLETED]:     trans('evaluation_completed_short', {}, 'evaluation'),
  [EVALUATION_STATUS_PASSED]:        trans('evaluation_passed_short', {}, 'evaluation')
}

export const constants = {
  // evaluation
  EVALUATION_STATUS_PRIORITY,
  EVALUATION_STATUS_COLOR,
  EVALUATION_STATUSES,
  EVALUATION_STATUSES_SHORT,

  EVALUATION_STATUS_NOT_ATTEMPTED,
  EVALUATION_STATUS_TODO,
  EVALUATION_STATUS_UNKNOWN,
  EVALUATION_STATUS_OPENED,
  EVALUATION_STATUS_INCOMPLETE,
  EVALUATION_STATUS_PARTICIPATED,
  EVALUATION_STATUS_COMPLETED,
  EVALUATION_STATUS_PASSED,
  EVALUATION_STATUS_FAILED
}
