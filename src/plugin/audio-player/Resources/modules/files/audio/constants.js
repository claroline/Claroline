import {trans} from '#/main/app/intl/translation'

const MANAGER_TYPE = 'manager'
const USER_TYPE = 'user'
const NO_TYPE = 'none'

const SECTIONS_TYPES = {
  [MANAGER_TYPE]: trans('section_type_manager', {}, 'audio'),
  [USER_TYPE]: trans('section_type_user', {}, 'audio'),
  [NO_TYPE]: trans('none')
}

export const constants = {
  MANAGER_TYPE,
  USER_TYPE,
  NO_TYPE,
  SECTIONS_TYPES
}