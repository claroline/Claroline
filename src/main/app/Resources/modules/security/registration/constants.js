import {trans} from '#/main/app/intl/translation'
import {constants as alertConstants} from '#/main/app/overlays/alert/constants'

const ALERT_REGISTRATION = {
  [alertConstants.ALERT_STATUS_PENDING]: {
    title: trans('register.pending.title', {}, 'alerts'),
    message: trans('register.pending.message', {}, 'alerts')
  },
  [alertConstants.ALERT_STATUS_SUCCESS]: {
    title: trans('register.success.title', {}, 'alerts'),
    message: trans('register.success.message', {}, 'alerts')
  },
  [alertConstants.ALERT_STATUS_WARNING]: {
    title: trans('register.warning.title', {}, 'alerts'),
    message: trans('register.warning.message', {}, 'alerts')
  },
  [alertConstants.ALERT_STATUS_ERROR]: {
    title: trans('register.error.title', {}, 'alerts'),
    message: trans('register.error.message', {}, 'alerts')
  }
}

const REGISTRATION_MAIL_VALIDATION_NONE = 0
const REGISTRATION_MAIL_VALIDATION_PARTIAL = 1
const REGISTRATION_MAIL_VALIDATION_FULL = 2

const registrationValidationTypes = {
  [REGISTRATION_MAIL_VALIDATION_NONE]: trans('none'),
  [REGISTRATION_MAIL_VALIDATION_PARTIAL]: trans('send_mail_info'),
  [REGISTRATION_MAIL_VALIDATION_FULL]: trans('force_mail_validation')
}

const ORGANIZATION_SELECTION_NONE = 'none'
const ORGANIZATION_SELECTION_CREATE = 'create'
const ORGANIZATION_SELECTION_SELECT = 'select'

const ORGANIZATION_SELECTION_CHOICES = {
  [ORGANIZATION_SELECTION_NONE]: trans('no_selection'),
  [ORGANIZATION_SELECTION_CREATE]: trans('force_organization_creation'),
  [ORGANIZATION_SELECTION_SELECT]: trans('allow_organization_selection')
}

export const constants = {
  ALERT_REGISTRATION,
  REGISTRATION_MAIL_VALIDATION_NONE,
  REGISTRATION_MAIL_VALIDATION_FULL,
  REGISTRATION_MAIL_VALIDATION_PARTIAL,
  registrationValidationTypes,
  ORGANIZATION_SELECTION_CHOICES,
  ORGANIZATION_SELECTION_NONE,
  ORGANIZATION_SELECTION_CREATE,
  ORGANIZATION_SELECTION_SELECT
}
