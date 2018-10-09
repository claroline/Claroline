import {trans} from '#/main/core/translation'

const REGISTRATION_MAIL_VALIDATION_NONE = 0
const REGISTRATION_MAIL_VALIDATION_FULL = 2
const REGISTRATION_MAIL_VALIDATION_PARTIAL = 1

const registrationValidationTypes = {
  [REGISTRATION_MAIL_VALIDATION_NONE]: trans('none'),
  [REGISTRATION_MAIL_VALIDATION_FULL]: trans('force_mail_validation'),
  [REGISTRATION_MAIL_VALIDATION_PARTIAL]: trans('send_mail_info')
}

export const constants = {
  REGISTRATION_MAIL_VALIDATION_NONE,
  REGISTRATION_MAIL_VALIDATION_FULL,
  REGISTRATION_MAIL_VALIDATION_PARTIAL,
  registrationValidationTypes
}
