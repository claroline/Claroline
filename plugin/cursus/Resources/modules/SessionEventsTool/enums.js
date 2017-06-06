import {trans, t} from '#/main/core/translation'

export const registrationTypes = [
  trans('event_registration_automatic', {}, 'cursus'),
  trans('event_registration_manual', {}, 'cursus'),
  trans('event_registration_public', {}, 'cursus')
]
export const registrationStatus = [
  t('registered'),
  t('pending')
]
export const VIEW_MANAGER = 'manager_mode'
export const VIEW_USER = 'user_mode'
export const VIEW_EVENT = 'event_view'