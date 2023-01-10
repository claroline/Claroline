import {trans} from '#/main/app/intl/translation'

import {constants as baseConst} from '#/main/evaluation/constants'

// evaluation
const EVALUATION_STATUSES = {
  [baseConst.EVALUATION_STATUS_NOT_ATTEMPTED]: trans('evaluation_not_attempted_status', {}, 'resource'),
  [baseConst.EVALUATION_STATUS_TODO]:          trans('evaluation_todo_status', {}, 'resource'),
  [baseConst.EVALUATION_STATUS_UNKNOWN]:       trans('evaluation_unknown_status', {}, 'resource'),
  [baseConst.EVALUATION_STATUS_OPENED]:        trans('evaluation_opened_status', {}, 'resource'),
  [baseConst.EVALUATION_STATUS_INCOMPLETE]:    trans('evaluation_incomplete_status', {}, 'resource'),
  [baseConst.EVALUATION_STATUS_PARTICIPATED]:  trans('evaluation_participated_status', {}, 'resource'),
  [baseConst.EVALUATION_STATUS_FAILED]:        trans('evaluation_failed_status', {}, 'resource'),
  [baseConst.EVALUATION_STATUS_COMPLETED]:     trans('evaluation_completed_status', {}, 'resource'),
  [baseConst.EVALUATION_STATUS_PASSED]:        trans('evaluation_passed_status', {}, 'resource')
}

export const constants = {
  EVALUATION_STATUSES
}
