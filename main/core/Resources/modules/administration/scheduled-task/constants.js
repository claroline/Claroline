import {trans} from '#/main/core/translation'

const TASK_TYPE_MAIL = 'email'
const TASK_TYPE_MESSAGE = 'message'

const TASK_TYPES = {
  [TASK_TYPE_MAIL]:    trans('email'),
  [TASK_TYPE_MESSAGE]: trans('message')
}

export const constants = {
  TASK_TYPES
}
