import {trans} from '#/main/app/intl/translation'

import {constants as baseConst} from '#/main/core/constants'

const SHORTCUTS_LIMIT = 8

const WORKSPACE_MODEL_TOOLS = ['home', 'resources', 'community', 'badges']

// evaluation
const EVALUATION_STATUSES = {
  [baseConst.EVALUATION_STATUS_NOT_ATTEMPTED]: trans('evaluation_not_attempted_status', {}, 'workspace'),
  [baseConst.EVALUATION_STATUS_TODO]:          trans('evaluation_todo_status', {}, 'workspace'),
  [baseConst.EVALUATION_STATUS_UNKNOWN]:       trans('evaluation_unknown_status', {}, 'workspace'),
  [baseConst.EVALUATION_STATUS_OPENED]:        trans('evaluation_opened_status', {}, 'workspace'),
  [baseConst.EVALUATION_STATUS_INCOMPLETE]:    trans('evaluation_incomplete_status', {}, 'workspace'),
  [baseConst.EVALUATION_STATUS_PARTICIPATED]:  trans('evaluation_participated_status', {}, 'workspace'),
  [baseConst.EVALUATION_STATUS_FAILED]:        trans('evaluation_failed_status', {}, 'workspace'),
  [baseConst.EVALUATION_STATUS_COMPLETED]:     trans('evaluation_completed_status', {}, 'workspace'),
  [baseConst.EVALUATION_STATUS_PASSED]:        trans('evaluation_passed_status', {}, 'workspace')
}

export const constants = {
  SHORTCUTS_LIMIT,
  EVALUATION_STATUSES,
  WORKSPACE_MODEL_TOOLS
}
