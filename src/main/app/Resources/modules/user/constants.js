import {trans} from '#/main/app/intl'

const USER_STATUS_ONLINE = 'online'
const USER_STATUS_ABSENT = 'absent'
const USER_STATUS_DONT_DISTURB = 'dont_disturb'
const USER_STATUS_OFFLINE = 'offline'

const USER_STATUSES = {
  [USER_STATUS_ONLINE]: trans('user_online'),
  [USER_STATUS_ABSENT]: trans('user_absent'),
  [USER_STATUS_DONT_DISTURB]: trans('user_dont_disturb'),
  [USER_STATUS_OFFLINE]: trans('user_offline')
}

const USER_STATUS_COLORS = {
  [USER_STATUS_ONLINE]: 'success',
  [USER_STATUS_ABSENT]: 'warning',
  [USER_STATUS_DONT_DISTURB]: 'danger',
  [USER_STATUS_OFFLINE]: 'secondary'
}

export const constants = {
  USER_STATUSES,
  USER_STATUS_ONLINE,
  USER_STATUS_ABSENT,
  USER_STATUS_DONT_DISTURB,
  USER_STATUS_OFFLINE,

  USER_STATUS_COLORS
}
