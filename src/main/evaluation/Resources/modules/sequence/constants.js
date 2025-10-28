import {trans} from '#/main/app/intl'

import {constants as baseConst} from '#/main/evaluation/constants'

const EVALUATION_STATUSES = {
  [baseConst.EVALUATION_STATUS_NOT_ATTEMPTED]: trans('evaluation_not_attempted_status', {}, 'sequence'),
  [baseConst.EVALUATION_STATUS_UNKNOWN]:       trans('evaluation_unknown_status', {}, 'sequence'),
  [baseConst.EVALUATION_STATUS_INCOMPLETE]:    trans('evaluation_incomplete_status', {}, 'sequence'),
  [baseConst.EVALUATION_STATUS_FAILED]:        trans('evaluation_failed_status', {}, 'sequence'),
  [baseConst.EVALUATION_STATUS_COMPLETED]:     trans('evaluation_completed_status', {}, 'sequence'),
  [baseConst.EVALUATION_STATUS_PASSED]:        trans('evaluation_passed_status', {}, 'sequence')
}

const PAGINATION_NONE = 'none'
const PAGINATION_STEP = 'step'
const PAGINATION_ALL = 'all'

const PAGINATIONS = {
  [PAGINATION_NONE]: {
    label: trans('sequence_pagination_none', {}, 'evaluation'),
    description: trans('sequence_pagination_none_help', {}, 'evaluation')
  },
  [PAGINATION_STEP]: {
    label: trans('sequence_pagination_step', {}, 'evaluation'),
    description: trans('sequence_pagination_step_help', {}, 'evaluation')
  },
  [PAGINATION_ALL]: {
    label: trans('sequence_pagination_all', {}, 'evaluation'),
    description: trans('sequence_pagination_all_help', {}, 'evaluation')
  }
}

export const constants = {
  PAGINATIONS,
  PAGINATION_NONE,
  PAGINATION_STEP,
  PAGINATION_ALL,
  EVALUATION_STATUSES
}
