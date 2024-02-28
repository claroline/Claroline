import {trans} from '#/main/app/intl/translation'

const REGISTRATION_AUTO = 0
const REGISTRATION_MANUAL = 1
const REGISTRATION_PUBLIC = 2

const REGISTRATION_TYPES = {
  [REGISTRATION_AUTO]: trans('event_registration_automatic', {}, 'cursus'),
  [REGISTRATION_MANUAL]: trans('event_registration_manual', {}, 'cursus'),
  [REGISTRATION_PUBLIC]: trans('event_registration_public', {}, 'cursus')
}

const PRESENCE_STATUS_UNKNOWN = 'unknown'
const PRESENCE_STATUS_PRESENT = 'present'
const PRESENCE_STATUS_ABSENT_JUSTIFIED = 'absent_justified'
const PRESENCE_STATUS_ABSENT_UNJUSTIFIED = 'absent_unjustified'

const PRESENCE_STATUSES = {
  [PRESENCE_STATUS_UNKNOWN]: trans('presence_unknown', {}, 'cursus'),
  [PRESENCE_STATUS_PRESENT]: trans('presence_present', {}, 'cursus'),
  [PRESENCE_STATUS_ABSENT_JUSTIFIED]: trans('presence_absent_justified', {}, 'cursus'),
  [PRESENCE_STATUS_ABSENT_UNJUSTIFIED]: trans('presence_absent_unjustified', {}, 'cursus')
}

const PRESENCE_STATUS_COLORS = {
  [PRESENCE_STATUS_UNKNOWN]: 'secondary',
  [PRESENCE_STATUS_PRESENT]: 'success',
  [PRESENCE_STATUS_ABSENT_JUSTIFIED]: 'warning',
  [PRESENCE_STATUS_ABSENT_UNJUSTIFIED]: 'danger'
}

const LEARNER_TYPE = 'learner'
const TEACHER_TYPE = 'tutor'

export const constants = {
  REGISTRATION_AUTO,
  REGISTRATION_MANUAL,
  REGISTRATION_PUBLIC,
  REGISTRATION_TYPES,
  PRESENCE_STATUS_UNKNOWN,
  PRESENCE_STATUS_PRESENT,
  PRESENCE_STATUS_ABSENT_JUSTIFIED,
  PRESENCE_STATUS_ABSENT_UNJUSTIFIED,
  PRESENCE_STATUSES,
  PRESENCE_STATUS_COLORS,
  LEARNER_TYPE,
  TEACHER_TYPE
}
