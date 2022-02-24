import {trans} from '#/main/app/intl/translation'

const TASK_STATUS_PENDING = 'pending'
const TASK_STATUS_IN_PROGRESS = 'in_progress'
const TASK_STATUS_SUCCESS = 'success'
const TASK_STATUS_ERROR = 'error'

const TASK_STATUSES = {
  [TASK_STATUS_PENDING]:     trans('task_pending', {}, 'scheduler'),
  [TASK_STATUS_IN_PROGRESS]: trans('task_in_progress', {}, 'scheduler'),
  [TASK_STATUS_SUCCESS]:     trans('task_success', {}, 'scheduler'),
  [TASK_STATUS_ERROR]:       trans('task_error', {}, 'scheduler')
}

const TASK_TYPE_ONCE = 'once'
const TASK_TYPE_RECURRING = 'recurring'

const TASK_TYPES = {
  [TASK_TYPE_ONCE]:      trans('once', {}, 'scheduler'),
  [TASK_TYPE_RECURRING]: trans('recurring', {}, 'scheduler')
}

// next ones are deprecated
const TASK_ACTION_MAIL = 'email'
const TASK_ACTION_MESSAGE = 'message'

const TASK_ACTIONS = {
  [TASK_ACTION_MAIL]:    trans('email'),
  [TASK_ACTION_MESSAGE]: trans('message')
}

export const constants = {
  TASK_TYPES,
  TASK_ACTIONS,
  TASK_STATUSES,
  TASK_STATUS_PENDING,
  TASK_STATUS_IN_PROGRESS,
  TASK_STATUS_SUCCESS,
  TASK_STATUS_ERROR
}
