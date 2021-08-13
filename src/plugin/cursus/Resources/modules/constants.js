import {trans} from '#/main/app/intl/translation'

const DEFAULT_ORDER = 1

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
  [PRESENCE_STATUS_UNKNOWN]: 'default',
  [PRESENCE_STATUS_PRESENT]: 'success',
  [PRESENCE_STATUS_ABSENT_JUSTIFIED]: 'warning',
  [PRESENCE_STATUS_ABSENT_UNJUSTIFIED]: 'danger'
}

const SUBSCRIPTION_STATUS_PENDING = 'pending'
const SUBSCRIPTION_STATUS_VALIDATED = 'validated'
const SUBSCRIPTION_STATUS_MANAGED = 'managed'
const SUBSCRIPTION_STATUS_REFUSED = 'refused'

const SUBSCRIPTION_STATUSES = {
  [SUBSCRIPTION_STATUS_PENDING]: trans('subscription_pending', {}, 'cursus'),
  [SUBSCRIPTION_STATUS_VALIDATED]: trans('subscription_validated', {}, 'cursus'),
  [SUBSCRIPTION_STATUS_MANAGED]: trans('subscription_managed', {}, 'cursus'),
  [SUBSCRIPTION_STATUS_REFUSED]: trans('subscription_refused', {}, 'cursus')
}

const SUBSCRIPTION_STATUS_COLORS = {
  [SUBSCRIPTION_STATUS_PENDING]: 'default',
  [SUBSCRIPTION_STATUS_VALIDATED]: 'success',
  [SUBSCRIPTION_STATUS_MANAGED]: 'warning',
  [SUBSCRIPTION_STATUS_REFUSED]: 'danger'
}

const LEARNER_TYPE = 'learner'
const TEACHER_TYPE = 'tutor'

export const constants = {
  DEFAULT_ORDER,
  REGISTRATION_AUTO,
  REGISTRATION_MANUAL,
  REGISTRATION_PUBLIC,
  REGISTRATION_TYPES,
  PRESENCE_STATUSES,
  PRESENCE_STATUS_COLORS,
  SUBSCRIPTION_STATUSES,
  SUBSCRIPTION_STATUS_COLORS,
  LEARNER_TYPE,
  TEACHER_TYPE
}
