import {trans} from '#/main/app/intl/translation'

const TASK_TYPE_MAIL = 'email'
const TASK_TYPE_MESSAGE = 'message'

const TASK_TYPES = {
  [TASK_TYPE_MAIL]:    trans('email'),
  [TASK_TYPE_MESSAGE]: trans('message')
}

const TASK_STATUS_PENDING = 'pending'
const TASK_STATUS_IN_PROGRESS = 'in_progress'
const TASK_STATUS_SUCCESS = 'success'
const TASK_STATUS_ERROR = 'error'

const TASK_STATUSES = {
  [TASK_STATUS_PENDING]: trans('task_pending', {}, 'scheduler'),
  [TASK_STATUS_IN_PROGRESS]: trans('task_in_progress', {}, 'scheduler'),
  [TASK_STATUS_SUCCESS]: trans('task_success', {}, 'scheduler'),
  [TASK_STATUS_ERROR]: trans('task_error', {}, 'scheduler')
}

export const constants = {
  TASK_TYPES,
  TASK_STATUSES,
  TASK_STATUS_PENDING,
  TASK_STATUS_IN_PROGRESS,
  TASK_STATUS_SUCCESS,
  TASK_STATUS_ERROR
}
