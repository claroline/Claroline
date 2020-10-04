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

const LEARNER_TYPE = 'learner'
const TEACHER_TYPE = 'tutor'

const VALIDATION = 1
const VALIDATION_USER = 2
const VALIDATION_VALIDATOR = 4
const VALIDATION_ORGANIZATION = 8

export const constants = {
  DEFAULT_ORDER,
  REGISTRATION_AUTO,
  REGISTRATION_MANUAL,
  REGISTRATION_PUBLIC,
  REGISTRATION_TYPES,
  LEARNER_TYPE,
  TEACHER_TYPE,
  VALIDATION,
  VALIDATION_USER,
  VALIDATION_VALIDATOR,
  VALIDATION_ORGANIZATION
}