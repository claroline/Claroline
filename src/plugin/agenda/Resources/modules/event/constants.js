import {trans} from '#/main/app/intl/translation'

const EVENT_TYPE_EVENT = 'event'
const EVENT_TYPE_TASK = 'task'

const EVENT_TYPES = {
  [EVENT_TYPE_EVENT]: trans('event'),
  [EVENT_TYPE_TASK]: trans('task')
}

export const constants = {
  EVENT_TYPE_EVENT,
  EVENT_TYPE_TASK,
  EVENT_TYPES
}
