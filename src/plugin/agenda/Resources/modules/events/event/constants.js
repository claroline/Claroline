import {trans} from '#/main/app/intl/translation'

const EVENT_STATUS_UNKNOWN = 'unknown'
const EVENT_STATUS_JOIN = 'join'
const EVENT_STATUS_MAYBE = 'maybe'
const EVENT_STATUS_RESIGN = 'resign'

const EVENT_STATUSES = {
  [EVENT_STATUS_UNKNOWN]: trans(EVENT_STATUS_UNKNOWN),
  [EVENT_STATUS_JOIN]: trans('accept_invitation', {}, 'agenda'),
  [EVENT_STATUS_MAYBE]: trans('accept_maybe_invitation', {}, 'agenda'),
  [EVENT_STATUS_RESIGN]: trans('decline_invitation', {}, 'agenda')
}

export const constants = {
  EVENT_STATUSES
}
