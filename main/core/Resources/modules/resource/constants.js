import {trans} from '#/main/core/translation'

// TODO : implement groups

// group
const RESOURCE_GROUP_GENERIC    = 'generic'
const RESOURCE_GROUP_EVALUATION = 'evaluation'
const RESOURCE_GROUP_COMMUNITY  = 'community'
const RESOURCE_GROUP_CONTENT    = 'content'

const RESOURCE_GROUPS = {
  [RESOURCE_GROUP_GENERIC]: {
    icon: '',
    label: ''
  },
  [RESOURCE_GROUP_EVALUATION]: {
    icon: '',
    label: ''
  },
  [RESOURCE_GROUP_COMMUNITY]: {
    icon: '',
    label: ''
  },
  [RESOURCE_GROUP_CONTENT]: {
    icon: '',
    label: ''
  }
}

// node
const RESOURCE_CLOSE_WS = 0
const RESOURCE_CLOSE_DESKTOP = 1

const RESOURCE_CLOSE_TARGETS = {
  [RESOURCE_CLOSE_WS]: trans('close_redirect_workspace', {}, 'resource'),
  [RESOURCE_CLOSE_DESKTOP]: trans('close_redirect_desktop', {}, 'resource')
}

// evaluation
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
  // group
  RESOURCE_GROUP_GENERIC,
  RESOURCE_GROUP_EVALUATION,
  RESOURCE_GROUP_COMMUNITY,
  RESOURCE_GROUP_CONTENT,
  RESOURCE_GROUPS,
  // node
  RESOURCE_CLOSE_WS,
  RESOURCE_CLOSE_DESKTOP,
  RESOURCE_CLOSE_TARGETS,
  // evaluation
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
