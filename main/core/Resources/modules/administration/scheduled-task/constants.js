import {trans} from '#/main/core/translation'

const TASK_TYPE_MAIL = 'mail'
const TASK_TYPE_MESSAGE = 'message'

const TASK_TYPES = {
  [TASK_TYPE_MAIL]:    trans('mail'),
  [TASK_TYPE_MESSAGE]: trans('message'),
}

export const constants = {
  TASK_TYPES
}
